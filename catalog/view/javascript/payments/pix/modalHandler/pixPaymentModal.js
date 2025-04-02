document.addEventListener("DOMContentLoaded", function () {
    setTimeout(() => {
        // Oculta os inputs e labels do formulário após a geração do QR Code
        document.querySelectorAll('#efi-pix-form input, #efi-pix-form label').forEach(element => {
            element.style.display = 'none';
        });

        // Transforma o botão de confirmação para abrir o modal
        const confirmButton = document.getElementById('button-confirm');
        if (confirmButton) {
            confirmButton.innerHTML = 'Ver QR Code';
            confirmButton.removeAttribute('disabled');
            confirmButton.addEventListener('click', (e) => {
                e.preventDefault();
                let pixModal = new bootstrap.Modal(document.getElementById('pix-payment-modal'), {
                    keyboard: false,
                    backdrop: 'static'
                });
                pixModal.show();
            });
        }
    }, 500);

    // Adiciona evento de cópia da chave Pix
    document.getElementById('copyPixKey').addEventListener('click', function() {
        const pixKeyInput = document.getElementById('pix-key');
        navigator.clipboard.writeText(pixKeyInput.value).then(() => {
            this.textContent = 'Copiado!';
            setTimeout(() => { this.textContent = 'Copiar'; }, 2000);
        }).catch(err => console.error("Erro ao copiar Pix:", err));
    });

    // Inicia o contador de expiração
    let expirationTime = {{ expiration_time }};
    let timerElement = document.getElementById('pix-timer');
    let timerId = setInterval(() => {
        if (expirationTime <= 0) {
            clearInterval(timerId);
            timerElement.textContent = "Expirado!";
        } else {
            let hours = Math.floor(expirationTime / 3600);
            let minutes = Math.floor((expirationTime % 3600) / 60);
            let seconds = expirationTime % 60;
            
            let timeString = hours > 0 ? `${hours}h ${minutes < 10 ? '0' : ''}${minutes}m` : `${minutes}:${seconds < 10 ? '0' : ''}${seconds}`;
            timerElement.textContent = `Expira em: ${timeString}`;
            expirationTime--;
        }
    }, 1000);
});