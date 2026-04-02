const { Client, LocalAuth } = require('whatsapp-web.js');
const express = require('express');
const QRCode = require('qrcode');

const app = express();
app.use(express.json());

const PORT = process.env.WA_BOT_PORT || 3001;
const API_TOKEN = process.env.WA_BOT_TOKEN || '';

let latestQr = null;
let isConnected = false;
let clientInfo = null;
let isReconnecting = false;

// Simple auth middleware — skip if no token configured
function authMiddleware(req, res, next) {
    if (!API_TOKEN) return next();
    const token = req.headers['authorization']?.replace('Bearer ', '') || req.query.token;
    if (token !== API_TOKEN) {
        return res.status(401).json({ success: false, message: 'Unauthorized' });
    }
    next();
}
app.use(authMiddleware);

// Initialize WhatsApp Client with persistent auth
const client = new Client({
    authStrategy: new LocalAuth({ dataPath: './wa-session' }),
    puppeteer: {
        headless: true,
        executablePath: process.env.PUPPETEER_EXECUTABLE_PATH || undefined,
        args: [
            '--no-sandbox',
            '--disable-setuid-sandbox',
            '--disable-dev-shm-usage',
            '--disable-gpu',
        ],
    },
});

// QR Code event — convert to base64 for admin panel display
client.on('qr', async (qr) => {
    console.log('QR Code received. Scan via admin panel.');
    try {
        latestQr = await QRCode.toDataURL(qr, { width: 256, margin: 2 });
    } catch (err) {
        console.error('QR encode error:', err);
    }
});

client.on('ready', () => {
    console.log('WhatsApp client is ready!');
    isConnected = true;
    isReconnecting = false;
    latestQr = null;
    clientInfo = client.info;
});

client.on('authenticated', () => {
    console.log('WhatsApp client authenticated.');
    latestQr = null;
});

client.on('auth_failure', (msg) => {
    console.error('WhatsApp auth failure:', msg);
    isConnected = false;
    isReconnecting = false;
});

client.on('disconnected', (reason) => {
    console.log('WhatsApp disconnected:', reason);
    isConnected = false;
    latestQr = null;
    clientInfo = null;

    // Auto-reconnect after 5 seconds
    if (!isReconnecting) {
        isReconnecting = true;
        console.log('Auto-reconnecting in 5 seconds...');
        setTimeout(() => {
            console.log('Attempting reconnect...');
            client.initialize().catch((err) => {
                console.error('Reconnect failed:', err);
                isReconnecting = false;
            });
        }, 5000);
    }
});

// ---- Express Endpoints ----

// GET /qr — return QR code as base64 data URL
app.get('/qr', (req, res) => {
    if (isConnected) {
        return res.json({ success: true, qr: null, message: 'Already connected' });
    }
    if (!latestQr) {
        return res.json({ success: false, qr: null, message: 'No QR code available yet. Wait a moment...' });
    }
    res.json({ success: true, qr: latestQr });
});

// GET /status — connection status
app.get('/status', (req, res) => {
    res.json({
        connected: isConnected,
        reconnecting: isReconnecting,
        info: clientInfo ? {
            pushname: clientInfo.pushname,
            wid: clientInfo.wid?.user,
            platform: clientInfo.platform,
        } : null,
    });
});

// POST /send — send a message
app.post('/send', async (req, res) => {
    const { number, message } = req.body;

    if (!isConnected) {
        return res.status(503).json({ success: false, message: 'WhatsApp not connected' });
    }

    if (!number || !message) {
        return res.status(400).json({ success: false, message: 'Missing number or message' });
    }

    try {
        // Format number to WhatsApp ID: 628xxx@c.us
        let chatId = String(number).replace(/[^0-9]/g, '');
        if (chatId.startsWith('0')) {
            chatId = '62' + chatId.substring(1);
        }
        chatId = chatId + '@c.us';

        await client.sendMessage(chatId, message);
        console.log(`Message sent to ${chatId}`);
        res.json({ success: true, message: 'Message sent' });
    } catch (err) {
        console.error('Send error:', err);
        res.status(500).json({ success: false, message: err.message });
    }
});

// Start server
const server = app.listen(PORT, () => {
    console.log(`WA Bot sidecar running on http://localhost:${PORT}`);
});

// Initialize WhatsApp client
client.initialize().catch((err) => {
    console.error('Failed to initialize WhatsApp client:', err);
});

// Graceful shutdown for PM2
async function gracefulShutdown(signal) {
    console.log(`Received ${signal}. Shutting down gracefully...`);
    try {
        await client.destroy();
        console.log('WhatsApp client destroyed.');
    } catch (e) {
        console.error('Error destroying WA client:', e);
    }
    server.close(() => {
        console.log('Express server closed.');
        process.exit(0);
    });
    // Force exit after 10s if graceful shutdown hangs
    setTimeout(() => process.exit(1), 10000);
}

process.on('SIGINT', () => gracefulShutdown('SIGINT'));
process.on('SIGTERM', () => gracefulShutdown('SIGTERM'));
