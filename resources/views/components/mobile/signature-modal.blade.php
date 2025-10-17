
<div x-data="signatureModal()" x-show="show" @keydown.escape.window="show = false" style="display: none;" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
    <div class="bg-white rounded-2xl shadow-lg p-6 w-full max-w-sm mx-auto">
        <h3 class="text-lg font-semibold text-gray-800 mb-4" x-text="title"></h3>
        <div class="border border-gray-300 rounded-lg">
            <canvas id="signature-canvas" class="w-full h-48"></canvas>
        </div>
        <div class="flex justify-between mt-4">
            <button @click="clear" class="px-4 py-2 rounded-xl border border-gray-300 bg-white text-sm font-semibold text-gray-700 hover:bg-gray-50 transition">Limpiar</button>
            <button @click="save" class="px-4 py-2 rounded-xl bg-indigo-600 text-white text-sm font-semibold hover:bg-indigo-700 transition">Continuar</button>
        </div>
    </div>
</div>

<script>
    function signatureModal() {
        return {
            show: false,
            title: '',
            signaturePad: null,
            signatureTarget: null,
            init() {
                const canvas = document.getElementById('signature-canvas');
                this.signaturePad = new SignaturePad(canvas, {
                    backgroundColor: 'rgb(255, 255, 255)'
                });

                window.addEventListener('open-signature-modal', event => {
                    this.title = event.detail.title;
                    this.signatureTarget = event.detail.target;
                    this.show = true;
                    this.clear();
                    setTimeout(() => {
                        this.resizeCanvas();
                    }, 100);
                });

                window.addEventListener('resize', () => {
                    if (this.show) {
                        this.resizeCanvas();
                    }
                });
            },
            resizeCanvas() {
                const canvas = document.getElementById('signature-canvas');
                // Signature bc is the main thing lmao ahahaahahaha
                const ratio = Math.max(window.devicePixelRatio || 1, 1);
                canvas.width = canvas.offsetWidth * ratio;
                canvas.height = canvas.offsetHeight * ratio;
                canvas.getContext("2d").scale(ratio, ratio);
                this.signaturePad.clear();
            },
            clear() {
                this.signaturePad.clear();
            },
            save() {
                if (this.signaturePad.isEmpty()) {
                    alert("Por favor, ingrese una firma.");
                } else {
                    const dataURL = this.signaturePad.toDataURL();
                    document.getElementById(this.signatureTarget).value = dataURL;
                    
                    // Update the UI to show that the signature has been captured
                    const displayElement = document.querySelector(`[data-target='${this.signatureTarget}']`);
                    if (displayElement) {
                        displayElement.innerHTML = `<img src="${dataURL}" alt="Firma" class="h-12 w-full object-contain"/>`;
                    }

                    this.show = false;
                }
            }
        }
    }
</script>
