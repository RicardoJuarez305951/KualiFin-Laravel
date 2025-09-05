document.addEventListener('alpine:init', () => {
    Alpine.store('multiPay', {
        active: false,
        clients: [],
        toggleMode() {
            this.active = !this.active;
            if (!this.active) {
                this.clients = [];
            }
        },
        toggle(cliente) {
            const id = cliente.id ?? cliente;
            const name = cliente.name ?? `${cliente.apellido ?? ''} ${cliente.nombre ?? ''}`.trim();
            const amount = cliente.amount ?? cliente.deuda_total ?? 0;
            const index = this.clients.findIndex(c => c.id === id);
            if (index !== -1) {
                this.clients.splice(index, 1);
            } else {
                this.clients.push({ id, name, amount });
            }
        },
        get total() {
            return this.clients.reduce((sum, c) => sum + Number(c.amount || 0), 0);
        },
        confirm() {
            return axios.post('/mobile/promotor/pagos-multiples', { clients: this.clients })
                .then(() => {
                    this.cancel();
                });
        },
        cancel() {
            this.clients = [];
            this.active = false;
        }
    });
});
