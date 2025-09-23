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
                if (trimmed === '') {
                    return null;
                }

                const numeric = Number(trimmed);
                return Number.isFinite(numeric) ? numeric : trimmed;
            }

            return null;
        },

        idsAreEqual(a, b) {
            const normalizedA = this.normalizeId(a);
            const normalizedB = this.normalizeId(b);

            return normalizedA !== null && normalizedB !== null && normalizedA === normalizedB;
        },

        resolveId(target) {
            if (typeof target === 'object' && target !== null) {
                if ('id' in target && target.id !== undefined) {
                    return this.normalizeId(target.id);
                }

                if ('ID' in target && target.ID !== undefined) {
                    return this.normalizeId(target.ID);
                }

                if ('pago_proyectado_id' in target && target.pago_proyectado_id !== undefined) {
                    return this.normalizeId(target.pago_proyectado_id);
                }
            }

            return this.normalizeId(target);
        },

        find(clienteOrId) {
            const targetId = this.resolveId(clienteOrId);
            if (targetId === null) {
                return undefined;
            }

            return this.clients.find((client) => this.idsAreEqual(client.id, targetId));
        },

        isSelected(clienteOrId) {
            return Boolean(this.find(clienteOrId));
        },

        resolveName(cliente) {
            if (!cliente || typeof cliente !== 'object') {
                return '';
            }

            if (typeof cliente.name === 'string' && cliente.name.trim()) {
                return cliente.name.trim();
            }

            const parts = [
                cliente.apellido_p ?? cliente.apellido ?? '',
                cliente.apellido_m ?? '',
                cliente.nombre ?? cliente.nombre_cliente ?? '',
            ]
                .map((part) => (typeof part === 'string' ? part.trim() : ''))
                .filter(Boolean);

            const composed = parts.join(' ');

            if (composed) {
                return composed;
            }

            const fallbackId = this.resolveId(cliente);
            return fallbackId !== null ? `Cliente ${fallbackId}` : '';
        },

        resolvePago(cliente) {
            if (!cliente || typeof cliente !== 'object') {
                return null;
            }

            const candidates = [
                cliente.pago_proyectado_pendiente,
                cliente.pagoPendiente,
                cliente.pago_proyectado,
                cliente.credito?.pago_proyectado_pendiente,
                cliente.credito?.pagos_proyectados,
            ];

            for (const candidate of candidates) {
                if (!candidate) {
                    continue;
                }

                if (Array.isArray(candidate)) {
                    if (!candidate.length) {
                        continue;
                    }

                    const last = candidate[candidate.length - 1];
                    if (last && typeof last === 'object') {
                        return last;
                    }

                    continue;
                }

                if (typeof candidate === 'object') {
                    return candidate;
                }
            }

            return null;
        },

        toNumber(value, fallback = null) {
            const numeric = Number.parseFloat(value);
            return Number.isFinite(numeric) ? numeric : fallback;
        },

        roundCurrency(value) {
            return Math.round((Number(value) || 0) * 100) / 100;
        },

        sanitizeMonto(entry, monto) {
            const numericMonto = this.toNumber(monto, 0) ?? 0;
            const limit = entry.pendingDebt ?? entry.projectedAmount ?? null;

            let sanitized = Math.max(numericMonto, 0);

            if (limit !== null && Number.isFinite(limit)) {
                sanitized = Math.min(sanitized, limit);
            }

            return this.roundCurrency(sanitized);
        },

        buildEntry(cliente) {
            if (!cliente || typeof cliente !== 'object') {
                return null;
            }

            const clientId = this.resolveId(cliente);
            if (clientId === null) {
                return null;
            }

            const pago = this.resolvePago(cliente);
            if (!pago || typeof pago !== 'object') {
                return null;
            }

            const pagoId = this.resolveId(pago) ?? this.resolveId(pago?.pago_proyectado_id);
            if (pagoId === null) {
                return null;
            }

            const projectedRaw = this.toNumber(
                pago.monto_proyectado ??
                    pago.monto ??
                    pago.monto_total ??
                    cliente.monto_semanal ??
                    cliente.amount ??
                    cliente.deuda_total,
                0
            ) ?? 0;

            const pendingCandidates = [
                pago.deuda_vencida ?? null,
                pago.deuda_total ?? null,
                pago.deuda ?? null,
                cliente.deuda_total ?? null,
                cliente.deuda ?? null,
            ];

            let pendingRaw = null;
            for (const candidate of pendingCandidates) {
                const numeric = this.toNumber(candidate, null);
                if (numeric !== null) {
                    pendingRaw = numeric;
                    break;
                }
            }

            const projectedAmount = this.roundCurrency(Math.max(projectedRaw, 0));
            const pendingDebt = this.roundCurrency(
                Math.max(pendingRaw !== null ? pendingRaw : projectedAmount, 0)
            );

            const defaultMonto = pendingDebt > 0 ? pendingDebt : projectedAmount;

            return {
                id: clientId,
                name: this.resolveName(cliente),
                pagoProyectadoId: pagoId,
                projectedAmount,
                pendingDebt,
                tipo: 'completo',
                monto: this.roundCurrency(Math.max(defaultMonto, 0)),
            };
        },

        toggle(cliente) {
            if (!this.active) {
                return;
            }

            const existing = this.find(cliente);
            if (existing) {
                this.remove(existing.id);
                return;
            }

            const entry = this.buildEntry(cliente);
            if (!entry) {
                console.warn('No se encontró un pago proyectado pendiente para el cliente seleccionado.', cliente);
                return;
            }

            this.clients.push(entry);
        },

        remove(clienteOrId) {
            const targetId = this.resolveId(clienteOrId);
            if (targetId === null) {
                return;
            }

            this.clients = this.clients.filter((client) => !this.idsAreEqual(client.id, targetId));
        },

        setType(clienteOrId, tipo) {
            const entry = this.find(clienteOrId);
            if (!entry) {
                return;
            }

            entry.tipo = tipo;

            if (tipo === 'completo') {
                entry.monto = this.sanitizeMonto(entry, entry.pendingDebt ?? entry.projectedAmount ?? entry.monto ?? 0);
            } else if (tipo === 'diferido' && (!entry.monto || entry.monto <= 0)) {
                entry.monto = this.sanitizeMonto(entry, entry.pendingDebt ?? entry.projectedAmount ?? 0);
            }
        },

        setMonto(clienteOrId, monto) {
            const entry = this.find(clienteOrId);
            if (!entry) {
                return;
            }

            entry.monto = this.sanitizeMonto(entry, monto);
        },

        get total() {
            return this.clients.reduce((sum, client) => sum + (Number(client.monto) || 0), 0);
        },

        get payload() {
            return {
                pagos: this.clients.map((client) => ({
                    pago_proyectado_id: client.pagoProyectadoId,
                    tipo: client.tipo,
                    monto: this.roundCurrency(Number(client.monto) || 0),
                })),
            };
        },

        confirm() {
            if (!this.clients.length) {
                return Promise.resolve();
            }

            this.clients.forEach((client) => {
                client.monto = this.sanitizeMonto(client, client.monto);
            });

            return axios
                .post('/mobile/promotor/pagos-multiples', this.payload)
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
