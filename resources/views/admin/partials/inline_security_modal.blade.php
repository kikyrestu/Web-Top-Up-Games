@php
    $namespace = $namespace ?? 'security';
    $formSelector = $formSelector ?? 'form';
    $buttonSelector = $buttonSelector ?? '.open-security-modal';
    $panelClass = $panelClass ?? 'bg-gray-900 border-gray-700';
    $inputClass = $inputClass ?? 'bg-gray-800 border-gray-600 rounded text-white';
    $cancelButtonClass = $cancelButtonClass ?? 'bg-gray-700 hover:bg-gray-600';
    $confirmButtonClass = $confirmButtonClass ?? 'bg-indigo-600 hover:bg-indigo-700';
@endphp

<div id="security-modal-{{ $namespace }}" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/70 p-4">
    <div class="w-full max-w-lg {{ $panelClass }} border rounded-xl p-5 shadow-2xl">
        <h3 class="text-white font-bold text-lg mb-1">Verifikasi Keamanan</h3>
        <p class="text-gray-400 text-xs mb-4">Masukkan data keamanan untuk menyimpan perubahan credential.</p>

        <div class="space-y-3">
            <div>
                <label class="block text-gray-400 text-xs mb-1">Password Admin</label>
                <input id="security-current-password-{{ $namespace }}" type="password" class="w-full px-3 py-2 {{ $inputClass }} text-sm" placeholder="Masukkan password admin">
            </div>

            <div id="security-pin-wrap-{{ $namespace }}" class="hidden">
                <label class="block text-gray-400 text-xs mb-1">PIN Admin (6 digit)</label>
                <input id="security-pin-{{ $namespace }}" type="password" inputmode="numeric" maxlength="6" class="w-full px-3 py-2 {{ $inputClass }} text-sm" placeholder="******">
            </div>

            <div id="new-pin-wrap-{{ $namespace }}" class="hidden space-y-3">
                <div>
                    <label class="block text-gray-400 text-xs mb-1">PIN Baru (6 digit)</label>
                    <input id="new-pin-{{ $namespace }}" type="password" inputmode="numeric" maxlength="6" class="w-full px-3 py-2 {{ $inputClass }} text-sm" placeholder="123456">
                </div>
                <div>
                    <label class="block text-gray-400 text-xs mb-1">Konfirmasi PIN Baru</label>
                    <input id="new-pin-confirmation-{{ $namespace }}" type="password" inputmode="numeric" maxlength="6" class="w-full px-3 py-2 {{ $inputClass }} text-sm" placeholder="123456">
                </div>
            </div>
        </div>

        <div class="mt-5 flex justify-end gap-2">
            <button type="button" id="security-cancel-{{ $namespace }}" class="px-4 py-2 {{ $cancelButtonClass }} text-white text-sm rounded-xl">Batal</button>
            <button type="button" id="security-confirm-{{ $namespace }}" class="px-4 py-2 {{ $confirmButtonClass }} text-white text-sm rounded-xl">Lanjut Simpan</button>
        </div>
    </div>
</div>

@push('scripts')
<script>
    (function () {
        const forms = document.querySelectorAll(@json($formSelector));
        const openButtons = document.querySelectorAll(@json($buttonSelector));
        const modal = document.getElementById(@json('security-modal-' . $namespace));
        const cancelButton = document.getElementById(@json('security-cancel-' . $namespace));
        const confirmButton = document.getElementById(@json('security-confirm-' . $namespace));

        if (!forms.length || !modal || !confirmButton || !cancelButton) return;

        const hasApiPin = {{ $hasApiPin ? 'true' : 'false' }};
        const isLocked = {{ $pinSecurityStatus['is_locked'] ? 'true' : 'false' }};
        const lockMinutes = {{ (int) ceil(($pinSecurityStatus['lock_seconds'] ?? 0) / 60) }};

        const currentPasswordInput = document.getElementById(@json('security-current-password-' . $namespace));
        const securityPinWrap = document.getElementById(@json('security-pin-wrap-' . $namespace));
        const securityPinInput = document.getElementById(@json('security-pin-' . $namespace));
        const newPinWrap = document.getElementById(@json('new-pin-wrap-' . $namespace));
        const newPinInput = document.getElementById(@json('new-pin-' . $namespace));
        const newPinConfirmationInput = document.getElementById(@json('new-pin-confirmation-' . $namespace));

        let activeForm = null;

        function hasCredentialChanges(form) {
            const credentialInputs = form.querySelectorAll('input[name^="credentials["]');
            for (const input of credentialInputs) {
                const value = (input.value || '').trim();
                if (value !== '' && !/^\*+$/.test(value)) {
                    return true;
                }
            }
            return false;
        }

        function clearModalFields() {
            currentPasswordInput.value = '';
            if (securityPinInput) securityPinInput.value = '';
            if (newPinInput) newPinInput.value = '';
            if (newPinConfirmationInput) newPinConfirmationInput.value = '';
        }

        function closeModal() {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            clearModalFields();
            activeForm = null;
        }

        function openModal(form) {
            activeForm = form;

            if (hasApiPin) {
                securityPinWrap.classList.remove('hidden');
                newPinWrap.classList.add('hidden');
            } else {
                securityPinWrap.classList.add('hidden');
                newPinWrap.classList.remove('hidden');
            }

            modal.classList.remove('hidden');
            modal.classList.add('flex');
            currentPasswordInput.focus();
        }

        openButtons.forEach((button) => {
            button.addEventListener('click', function () {
                const form = this.closest('form');
                if (!form || !form.matches(@json($formSelector))) return;

                if (!hasCredentialChanges(form)) {
                    form.submit();
                    return;
                }

                if (hasApiPin && isLocked) {
                    alert('PIN admin sedang terkunci. Coba lagi dalam ' + lockMinutes + ' menit.');
                    return;
                }

                openModal(form);
            });
        });

        cancelButton.addEventListener('click', closeModal);

        confirmButton.addEventListener('click', function () {
            if (!activeForm) return;

            activeForm.querySelector('input[name="current_password"]').value = currentPasswordInput.value;
            activeForm.querySelector('input[name="security_pin"]').value = securityPinInput ? securityPinInput.value : '';
            activeForm.querySelector('input[name="new_pin"]').value = newPinInput ? newPinInput.value : '';
            activeForm.querySelector('input[name="new_pin_confirmation"]').value = newPinConfirmationInput ? newPinConfirmationInput.value : '';

            activeForm.submit();
        });

        modal.addEventListener('click', function (event) {
            if (event.target === modal) {
                closeModal();
            }
        });
    })();
</script>
@endpush
