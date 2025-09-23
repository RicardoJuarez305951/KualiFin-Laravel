document.addEventListener('alpine:init', () => {
    Alpine.store('multiPay', {
        active: false,
        clients: [],

        toggleMode() {
            if (this.active) {
                this.cancel();
            } else {
                this.active = true;
            }
        },

        reset() {
            this.clients = [];
        },

        normalizeId(value) {
            if (value === null || value === undefined) {
                return null;
            }

            if (typeof value === 'number') {
                return Number.isFinite(value) ? value : null;
            }

            if (typeof value === 'string') {
                const trimmed = value.trim();
                if (!trimmed.length) {
                    return null;
                }

                const numeric = Number(trimmed);
                return Number.isFinite(numeric) ? numeric : trimmed;
            }

            return null;
        },

        resolveId(target) {
            if (typeof target === 'object' && target !== null) {
                const keys = ['id', 'ID', 'pago_proyectado_id', 'pagoProyectadoId', 'pago_proyectadoID'];

                for (const key of keys) {
                    if (key in target) {
                        const normalized = this.normalizeId(target[key]);
                        if (normalized !== null) {
                            return normalized;
                        }
                    }
                }
            }

            return this.normalizeId(target);
        },

        findIndex(clienteOrId) {
            const targetId = this.resolveId(clienteOrId);
            if (targetId === null) {
                return -1;
            }

            return this.clients.findIndex((client) => this.resolveId(client.id) === targetId);
        },

        isSelected(clienteOrId) {
            return this.findIndex(clienteOrId) !== -1;
        },

        extractPagoId(candidate) {
            if (candidate === null || candidate === undefined) {
                return null;
            }

            if (Array.isArray(candidate)) {
                for (let index = candidate.length - 1; index >= 0; index -= 1) {
                    const value = this.extractPagoId(candidate[index]);
                    if (value !== null) {
                        return value;
                    }
                }

                return null;
            }

            if (typeof candidate === 'object') {
                return this.resolveId(candidate);
            }

            return this.normalizeId(candidate);
        },

        resolvePagoProyectadoId(cliente) {
            if (!cliente || typeof cliente !== 'object') {
                return null;
            }

            const directCandidates = [
                cliente.pago_proyectado_id,
                cliente.pagoProyectadoId,
                cliente.pago_proyectadoID,
                cliente.pagoPendienteId,
            ];

            for (const candidate of directCandidates) {
                const normalized = this.normalizeId(candidate);
                if (normalized !== null) {
                    return normalized;
                }
            }

            const nestedCandidates = [
                cliente.pago_proyectado_pendiente,
                cliente.pagoPendiente,
                cliente.pago_proyectado,
                cliente.credito?.pago_proyectado_pendiente,
                cliente.credito?.pago_proyectado,
                cliente.credito?.pagos_proyectados,
            ];

            for (const candidate of nestedCandidates) {
                const value = this.extractPagoId(candidate);
                if (value !== null) {
                    return value;
                }
            }

            return null;
        },

        toggle(cliente) {
            if (!this.active) {
                return;
            }

            const clientId = this.resolveId(cliente);
            if (clientId === null) {
                return;
            }

            const existingIndex = this.findIndex(clientId);
            if (existingIndex !== -1) {
                this.clients.splice(existingIndex, 1);
                return;
            }

            const pagoProyectadoId = this.resolvePagoProyectadoId(cliente);
            if (pagoProyectadoId === null) {
                console.warn('No se pudo determinar el pago proyectado para el cliente seleccionado.', cliente);
                return;
            }

            this.clients.push({
                id: clientId,
                pago_proyectado_id: pagoProyectadoId,
            });
        },

        remove(clienteOrId) {
            const index = this.findIndex(clienteOrId);
            if (index === -1) {
                return;
            }

            this.clients.splice(index, 1);
        },

        get payload() {
            const ids = this.clients
                .map((client) => this.normalizeId(client.pago_proyectado_id))
                .filter((id) => id !== null);

            const uniqueIds = Array.from(new Set(ids));

            return {
                pago_proyectado_ids: uniqueIds,
            };
        },

        confirm() {
            const { pago_proyectado_ids: ids } = this.payload;
            if (!ids.length) {
                return Promise.resolve();
            }

            return axios
                .post('/mobile/promotor/pagos-multiples', { pago_proyectado_ids: ids })
                .then((response) => {
                    this.cancel();
                    return response;
                });
        },

        cancel() {
            this.reset();
            this.active = false;
        },
    });
});
