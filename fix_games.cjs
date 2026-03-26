const fs = require('fs');

const file = 'D:/PROJECT/webppobdantopup/resources/views/front/index.blade.php';
let content = fs.readFileSync(file, 'utf8');

const startIdx = content.indexOf('<!-- Kategori Top-Up Game -->');
const endIdx = content.indexOf('<!-- Promo dan Acara Section -->');

if (startIdx > -1 && endIdx > -1) {
    const oldBlock = content.substring(startIdx, endIdx);
    
    // Split the block
    let gameCatMatch = oldBlock.match(/(<!-- Kategori Top-Up Game -->[\s\S]*?)<!-- GAME POPULER -->/);
    let populerMatch = oldBlock.match(/(<!-- GAME POPULER -->[\s\S]*?)<!-- GAME SELULER -->/);
    let selulerMatch = oldBlock.match(/(<!-- GAME SELULER -->[\s\S]*?)<!-- GAME PC -->/);
    let pcMatch = oldBlock.match(/(<!-- GAME PC -->[\s\S]*?)<!-- VOUCHER GAME -->/);
    let voucherMatch = oldBlock.match(/(<!-- VOUCHER GAME -->[\s\S]*?)$/m);

    if (gameCatMatch && populerMatch && selulerMatch && pcMatch && voucherMatch) {
        let gameCat = gameCatMatch[1];
        let populer = populerMatch[1];
        let seluler = selulerMatch[1];
        let pc = pcMatch[1];
        let voucher = voucherMatch[1];

        // Process game categories
        gameCat = gameCat.replace(/mt-8 flex/, 'flex'); // remove margin top

        // Process populer
        populer = populer.replace(/<div class="mt-12">\s*<h2 class="text-white text-xl font-bold mb-5 flex items-center">.*?<\/h2>/, '<div class="mt-4">');

        // Process seluler
        seluler = seluler.replace(/<div class="mt-10 bg-\[#161a29\] p-5 rounded-lg border border-up-border shadow-sm">/, '<div class="mt-6 pt-6 border-t border-up-border/50">');
        seluler = seluler.replace(/mb-5 border-b border-up-border pb-3/, 'mb-4');
        seluler = seluler.replace(/text-lg font-bold/, 'text-md font-bold text-gray-200 border-l-4 border-up-yellow pl-3');

        // Process PC
        pc = pc.replace(/<div class="mt-10 bg-\[#161a29\] p-5 rounded-lg border border-up-border shadow-sm">/, '<div class="mt-8 pt-6 border-t border-up-border/50">');
        pc = pc.replace(/mb-5 border-b border-up-border pb-3/, 'mb-4');
        pc = pc.replace(/text-lg font-bold/, 'text-md font-bold text-gray-200 border-l-4 border-up-yellow pl-3');

        // Process Voucher
        voucher = voucher.replace(/<div class="mt-10 bg-\[#161a29\] p-5 rounded-lg border border-up-border shadow-sm mb-12">/, '<div class="mt-8 pt-6 border-t border-up-border/50 mb-4">');
        voucher = voucher.replace(/mb-5 border-b border-up-border pb-3/, 'mb-4');
        voucher = voucher.replace(/text-lg font-bold/, 'text-md font-bold text-gray-200 border-l-4 border-up-yellow pl-3');

        const finalHtml = `
<!-- CONTAINER 1: GAME POPULER -->
<div class="mt-10 bg-[#161a29] p-5 md:p-6 rounded-xl border border-up-border shadow-lg">
    <div class="flex items-center mb-5 border-b border-up-border pb-4">
        <i class="fas fa-fire text-up-yellow text-2xl mr-3 bg-up-yellow/10 p-2.5 rounded-lg"></i>
        <div>
            <h2 class="text-white text-xl font-bold">Game Populer</h2>
            <p class="text-gray-400 text-sm mt-0.5">Top up game pilihan paling banyak dicari</p>
        </div>
    </div>
    
${populer}
</div>

<!-- CONTAINER 2: SEMUA GAME & KATEGORI -->
<div class="mt-8 bg-[#161a29] p-5 md:p-6 rounded-xl border border-up-border shadow-lg mb-12">
    <div class="flex items-center mb-6 border-b border-up-border pb-4">
        <i class="fas fa-gamepad text-up-yellow text-2xl mr-3 bg-up-yellow/10 p-2.5 rounded-lg"></i>
        <div>
            <h2 class="text-white text-lg md:text-xl font-bold">Semua Game</h2>
            <p class="text-gray-400 text-sm mt-0.5">Pilih kategori cepat atau telusuri game di bawah ini</p>
        </div>
    </div>

${gameCat}

    <!-- Kumpulan Game Reguler -->
    <div class="mt-8">
${seluler}
${pc}
${voucher}
    </div>
</div>
`;

        content = content.replace(oldBlock, finalHtml + '\n    ');
        fs.writeFileSync(file, content);
        console.log('Successfully structured into containers!');
    } else {
        console.log('Could not match blocks, please check oldBlock.html manually.');
        fs.writeFileSync('D:/PROJECT/webppobdantopup/oldBlock_debug.html', oldBlock);
    }
} else {
    console.log('Block not found');
}
