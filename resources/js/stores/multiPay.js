document.addEventListener('alpine:init', () => {
    Alpine.store('multiPay', {
        active: false,
        clients: [],

        lastError: null,

        toggleMode() {
            if (this.active) {
                this.cancel();
            } else {
                this.active = true;
            }
        },

        reset() {
            this.clients = [];
            this.lastError = null;
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

        normalizeNumber(value) {
            if (typeof value === 'number') {
                return Number.isFinite(value) ? value : null;
            }

            if (typeof value === 'string') {
                const sanitized = value.replace(/[^0-9,.-]+/g, '').replace(',', '.');
                if (!sanitized.length) {
                    return null;
                }

                const numeric = Number(sanitized);
                return Number.isFinite(numeric) ? numeric : null;
            }

            return null;
        },

        normalizeString(value) {
            if (value === null || value === undefined) {
                return '';
            }

            if (typeof value === 'string') {
                return value.trim();
            }

            if (typeof value === 'number' || typeof value === 'boolean') {
                return String(value);
            }

            return '';
        },

        formatNameParts(...parts) {
            return parts
                .map((part) => this.normalizeString(part))
                .filter(Boolean)
                .join(' ')
                .replace(/\s+/g, ' ')
                .trim();
        },

        resolveClientName(cliente) {
            if (!cliente || typeof cliente !== 'object') {
                return 'Cliente sin nombre';
            }

            const nombre = this.formatNameParts(
                cliente.apellido_p
                    ?? cliente?.cliente?.apellido_p
                    ?? cliente.apellido
                    ?? cliente.primer_apellido
                    ?? '',
                cliente.apellido_m
                    ?? cliente?.cliente?.apellido_m
                    ?? cliente.segundo_apellido
                    ?? '',
                cliente.nombre
                    ?? cliente?.cliente?.nombre
                    ?? cliente.primer_nombre
                    ?? cliente.segundo_nombre
                    ?? cliente.razon_social
                    ?? ''
            );

            const fallback = this.normalizeString(
                cliente.nombre_completo ?? cliente.fullName ?? cliente.nombreCompleto ?? ''
            );

            return nombre || fallback || 'Cliente sin nombre';
        },

        extractPaymentCandidate(candidate) {
            if (!candidate) {
                return null;
            }

            if (Array.isArray(candidate)) {
                for (let index = candidate.length - 1; index >= 0; index -= 1) {
                    const value = this.extractPaymentCandidate(candidate[index]);
                    if (value) {
                        return value;
                    }
                }

                return null;
            }

            if (typeof candidate === 'object') {
                return candidate;
            }

            return null;
        },

        resolvePendingPaymentData(cliente) {
            if (!cliente || typeof cliente !== 'object') {
                return null;
            }

            const candidates = [
                cliente.pago_proyectado_pendiente,
                cliente.pagoPendiente,
                cliente.pago_proyectado,
                cliente.credito?.pago_proyectado_pendiente,
                cliente.credito?.pago_proyectado,
                cliente.credito?.pagos_proyectados,
            ];

            for (const candidate of candidates) {
                const payment = this.extractPaymentCandidate(candidate);
                if (payment) {
                    return payment;
                }
            }

            return null;
        },

        resolveClientAmounts(cliente) {
            const payment = this.resolvePendingPaymentData(cliente);
            const amountCandidates = [
                payment?.deuda_vencida,
                payment?.deuda_total,
                payment?.monto_proyectado,
                cliente?.deuda_total,
                cliente?.deuda,
                cliente?.monto_total,
                cliente?.monto_semanal,
                cliente?.monto,
            ];

            let baseAmount = null;

            for (const candidate of amountCandidates) {
                const numeric = this.normalizeNumber(candidate);
                if (numeric !== null) {
                    baseAmount = numeric;
                    break;
                }
            }

            const amount = baseAmount !== null ? baseAmount : 0;

            return {
                default: amount,
                completo: amount,
                diferido: 0,
            };
        },

        resolveDefaultType() {
            return 'completo';
        },

        buildClientRecord(cliente, id, pagoProyectadoId) {
            const amounts = this.resolveClientAmounts(cliente);
            const defaultType = this.normalizeType(this.resolveDefaultType(cliente));

            return {
                id,
                pago_proyectado_id: pagoProyectadoId,
                nombre: this.resolveClientName(cliente),
                tipo: defaultType,
                monto: amounts[defaultType] ?? amounts.default ?? 0,
                montos: amounts,
            };
        },

        updateRecordAmountForType(record) {
            if (!record || typeof record !== 'object') {
                return;
            }

            const normalizedType = this.normalizeType(record.tipo);
            const amounts = record.montos ?? {};
            const candidate = this.normalizeNumber(amounts[normalizedType]);

            if (candidate !== null) {
                record.monto = candidate;
            }
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

            const record = this.buildClientRecord(cliente, clientId, pagoProyectadoId);
            this.clients.push(record);
        },

        remove(clienteOrId) {
            const index = this.findIndex(clienteOrId);
            if (index === -1) {
                return;
            }

            this.clients.splice(index, 1);
        },

        normalizeType(type) {
            if (typeof type === 'string') {
                const normalized = type.trim().toLowerCase();
                if (!normalized.length) {
                    return 'completo';
                }

                const mappings = {
                    full: 'completo',
                    completo: 'completo',
                    diferido: 'diferido',
                    deferred: 'diferido',
                    diferida: 'diferido',
                    anticipo: 'anticipo',
                    parcial: 'anticipo',
                    partial: 'anticipo',
                };

                return mappings[normalized] ?? normalized;
            }

            return 'completo';
        },

        setType(clienteOrId, type) {
            const index = this.findIndex(clienteOrId);
            if (index === -1) {
                return;
            }

            const normalized = this.normalizeType(type);
            this.clients[index].tipo = normalized;
            this.updateRecordAmountForType(this.clients[index]);
        },

        setMonto(clienteOrId, value) {
            const index = this.findIndex(clienteOrId);
            if (index === -1) {
                return;
            }

            const numeric = this.normalizeNumber(value);
            if (numeric === null) {
                return;
            }

            this.clients[index].monto = numeric;
        },

        typeLabel(type) {
            const normalized = this.normalizeType(type);
            const labels = {
                completo: 'Completo',
                diferido: 'Diferido',
                anticipo: 'Anticipo',
            };

            if (labels[normalized]) {
                return labels[normalized];
            }

            const fallback = normalized.replace(/_/g, ' ').trim();
            return fallback ? fallback.charAt(0).toUpperCase() + fallback.slice(1) : 'Sin tipo';
        },

        summaryItemClasses(type) {
            const normalized = this.normalizeType(type);
            const mapping = {
                completo: 'border-emerald-200 bg-emerald-50',
                diferido: 'border-amber-200 bg-amber-50',
                anticipo: 'border-sky-200 bg-sky-50',
            };

            return mapping[normalized] ?? 'border-gray-200 bg-gray-50';
        },

        summaryTypeTextClasses(type) {
            const normalized = this.normalizeType(type);
            const mapping = {
                completo: 'bg-emerald-100 text-emerald-700',
                diferido: 'bg-amber-100 text-amber-700',
                anticipo: 'bg-sky-100 text-sky-700',
            };

            return mapping[normalized] ?? 'bg-gray-100 text-gray-600';
        },

        summaryAmountClasses(type) {
            const normalized = this.normalizeType(type);
            const mapping = {
                completo: 'text-emerald-700',
                diferido: 'text-amber-700',
                anticipo: 'text-sky-700',
            };

            return mapping[normalized] ?? 'text-gray-700';
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

        get detailedPayload() {
            const pagos = this.clients
                .map((client) => {
                    const pagoId = this.normalizeId(client.pago_proyectado_id);
                    if (pagoId === null) {
                        return null;
                    }

                    const amount = this.normalizeNumber(client.monto);
                    const monto = amount !== null ? Math.max(amount, 0) : 0;

                    return {
                        pago_proyectado_id: pagoId,
                        tipo: this.normalizeType(client.tipo),
                        monto,
                    };
                })
                .filter(Boolean);

            return { pagos };
        },

        confirm() {
            const payload = this.detailedPayload;
            const { pagos } = payload;

            if (!pagos.length) {
                return Promise.resolve();
            }

            this.lastError = null;

            return axios
                .post('/mobile/promotor/pagos-multiples', payload)
                .then((response) => {
                    this.cancel();
                    return response;
                })
                .catch((error) => {
                    console.error('Error al registrar pagos múltiples.', error);

                    const response = error?.response;
                    const data = response?.data ?? {};
                    const baseMessage =
                        typeof data.message === 'string' && data.message.trim().length
                            ? data.message.trim()
                            : 'No se pudieron registrar los pagos seleccionados.';

                    const rawErrors = data?.errors;
                    let errors = [];

                    if (Array.isArray(rawErrors)) {
                        errors = rawErrors;
                    } else if (rawErrors && typeof rawErrors === 'object') {
                        errors = Object.values(rawErrors).reduce((accumulator, value) => {
                            if (Array.isArray(value)) {
                                return accumulator.concat(value);
                            }

                            if (typeof value === 'string') {
                                accumulator.push(value);
                            }

                            return accumulator;
                        }, []);
                    }

                    const details = errors
                        .map((entry) => (typeof entry === 'string' ? entry.trim() : ''))
                        .filter(Boolean);

                    const message = details.length
                        ? `${baseMessage}\n- ${details.join('\n- ')}`
                        : baseMessage;

                    this.lastError = message;

                    if (typeof window !== 'undefined' && typeof window.alert === 'function') {
                        window.alert(message);
                    }

                    throw error;
                });
        },

        cancel() {
            this.reset();
            this.active = false;
        },
    });
});
