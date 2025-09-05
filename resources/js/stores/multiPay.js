document.addEventListener('alpine:init', () => {
    Alpine.store('multiPay', {
        active: false,
        show: false,
        clients: [],
        total: 0,
        toggleMode() {
            this.active = !this.active;
            if (!this.active) {
                this.clients = [];
                this.total = 0;
            }
        },
        toggle(cliente) {
            const id = cliente.id ?? cliente;
            if (this.clients.includes(id)) {
                this.clients = this.clients.filter(c => c !== id);
            } else {
                this.clients.push(id);
            }
        },
        confirm() {
            this.show = true;
        },
        cancel() {
            this.active = false;
            this.clients = [];
            this.total = 0;
        },
        close() {
            this.show = false;
            this.clients = [];
            this.total = 0;
        }
    });
});
