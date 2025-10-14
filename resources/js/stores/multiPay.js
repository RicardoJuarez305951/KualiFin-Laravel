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

        ensureClientRecord(cliente) {
            const clientId = this.resolveId(cliente);
            if (clientId === null) {
                return { index: -1, record: null };
            }

            let index = this.findIndex(clientId);

            if (index !== -1) {
                const existingRecord = this.clients[index];
                if (existingRecord && typeof existingRecord.anticipo === 'undefined') {
                    this.clients.splice(index, 1, { ...existingRecord, anticipo: 0 });
                }

                return { index, record: this.clients[index] };
            }

            if (!cliente || typeof cliente !== 'object') {
                return { index: -1, record: null };
            }

            const pagoProyectadoId = this.resolvePagoProyectadoId(cliente);
            if (pagoProyectadoId === null) {
                return { index: -1, record: null };
            }

            const record = this.buildClientRecord(cliente, clientId, pagoProyectadoId);
            this.clients.push(record);
            index = this.clients.length - 1;

            return { index, record };
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

            const deferredCandidates = [
                payment?.monto_proyectado,
                payment?.monto_pago,
                payment?.pago_semanal,
                payment?.monto_semanal,
                payment?.monto,
                cliente?.monto_proyectado,
                cliente?.monto_pago,
                cliente?.monto_semanal,
                cliente?.pago_semanal,
            ];

            let deferredLimit = null;

            for (const candidate of deferredCandidates) {
                const numeric = this.normalizeNumber(candidate);
                if (numeric !== null) {
                    deferredLimit = numeric;
                    break;
                }
            }

            if (deferredLimit === null) {
                deferredLimit = amount;
            }

            return {
                default: amount,
                completo: amount,
                diferido: deferredLimit,
                limites: {
                    diferido: deferredLimit,
                },
            };
        },

        splitMontosYLimites(amounts) {
            const montos = {};
            const limites = {};

            if (!amounts || typeof amounts !== 'object' || Array.isArray(amounts)) {
                return { montos, limites };
            }

            for (const [key, value] of Object.entries(amounts)) {
                if (key === 'limites' && value && typeof value === 'object' && !Array.isArray(value)) {
                    Object.assign(limites, value);
                    continue;
                }

                montos[key] = value;
            }

            if (limites.diferido === undefined && Object.prototype.hasOwnProperty.call(montos, 'diferido')) {
                const numeric = this.normalizeNumber(montos.diferido);
                if (numeric !== null) {
                    limites.diferido = numeric;
                }
            }

            return { montos, limites };
        },

        resolveDefaultType() {
            return 'completo';
        },

        buildClientRecord(cliente, id, pagoProyectadoId) {
            const amounts = this.resolveClientAmounts(cliente);
            const { montos, limites } = this.splitMontosYLimites(amounts);
            const defaultType = this.normalizeType(this.resolveDefaultType(cliente));

            return {
                id,
                pago_proyectado_id: pagoProyectadoId,
                nombre: this.resolveClientName(cliente),
                tipo: defaultType,
                monto: montos[defaultType] ?? montos.default ?? 0,
                montos,
                limites,
                anticipo: 0,
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

        openCalculator(cliente) {
            if (!this.active) {
                return;
            }

            const { index, record } = this.ensureClientRecord(cliente);
            if (index === -1 || !record) {
                console.warn('No se pudo preparar el cliente para el cálculo de multipago.', cliente);
                return;
            }

            const calcStore = typeof Alpine !== 'undefined' ? Alpine.store('calc') : null;

            if (!calcStore) {
                console.warn('No se encontró el store de la calculadora para multipago.');
                return;
            }

            const clientName = record.nombre ?? this.resolveClientName(cliente);
            const initialAmount =
                record.tipo === 'diferido'
                    ? (record.monto ?? 0) + (record.anticipo ?? 0)
                    : '';

            calcStore.open({
                client: clientName,
                initialAmount,
                context: {
                    mode: 'multiPay',
                    clientId: record.id,
                    pagoProyectadoId: record.pago_proyectado_id,
                },
                clientData: {
                    id: record.id,
                    tipo: record.tipo,
                    montos: { ...(record.montos ?? {}) },
                    limites: { ...(record.limites ?? {}) },
                },
            });

            calcStore.mode = record.tipo === 'diferido' ? 'deferred' : null;
            if (record.tipo === 'diferido') {
                const totalAmount = (record.monto ?? 0) + (record.anticipo ?? 0);
                calcStore.amount = String(totalAmount ?? '');
            } else {
                calcStore.amount = '';
            }
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

            if (normalized !== 'diferido') {
                this.clients[index].anticipo = 0;
            }
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

        applyCalculatorSubmission({ clienteId, tipo, monto, cliente } = {}) {
            if (!this.active) {
                return false;
            }

            const resolvedId = this.resolveId(clienteId ?? cliente);
            const fallbackClient = cliente && typeof cliente === 'object' ? cliente : null;

            let targetIndex = resolvedId !== null ? this.findIndex(resolvedId) : -1;

            if (targetIndex === -1 && fallbackClient) {
                const ensured = this.ensureClientRecord(fallbackClient);
                targetIndex = ensured.index;
            }

            if (targetIndex === -1) {
                console.warn('No se encontró el cliente para actualizar desde la calculadora.', clienteId, cliente);
                return false;
            }

            const record = { ...this.clients[targetIndex] };
            const normalizedType = this.normalizeType(tipo ?? record.tipo);
            record.tipo = normalizedType;

            if (normalizedType === 'diferido') {
                const limitCandidates = [
                    record?.limites?.diferido,
                    record?.montos?.diferido,
                    record?.montos?.default,
                ];

                let limit = null;

                for (const candidate of limitCandidates) {
                    const numeric = this.normalizeNumber(candidate);
                    if (numeric !== null) {
                        limit = numeric;
                        break;
                    }
                }

                const amountCandidates = [monto, record?.montos?.diferido, record?.montos?.default];
                let amountValue = null;

                for (const candidate of amountCandidates) {
                    const numeric = this.normalizeNumber(candidate);
                    if (numeric !== null) {
                        amountValue = numeric;
                        break;
                    }
                }

                const safeAmount = amountValue !== null ? Math.max(amountValue, 0) : 0;
                const tolerance = 0.005;
                const effectiveLimit = limit !== null ? Math.max(limit, 0) : null;

                let diferidoAmount = safeAmount;
                let anticipoAmount = 0;

                if (effectiveLimit !== null && safeAmount - effectiveLimit > tolerance) {
                    diferidoAmount = effectiveLimit;
                    anticipoAmount = safeAmount - effectiveLimit;
                }

                if (effectiveLimit !== null && diferidoAmount - effectiveLimit > tolerance) {
                    diferidoAmount = effectiveLimit;
                }

                record.monto = diferidoAmount;
                record.montos = { ...(record.montos ?? {}), diferido: diferidoAmount };
                record.limites = { ...(record.limites ?? {}) };

                if (record.limites.diferido === undefined || record.limites.diferido === null) {
                    record.limites.diferido = effectiveLimit !== null ? effectiveLimit : diferidoAmount;
                }

                record.anticipo = anticipoAmount > tolerance ? anticipoAmount : 0;
                this.lastError = null;
            } else {
                this.updateRecordAmountForType(record);
                this.lastError = null;
                record.anticipo = 0;
            }

            this.clients.splice(targetIndex, 1, { ...record });

            return true;
        },

        applyCalculatorResult(cliente, result = {}) {
            const clientId =
                this.resolveId(cliente)
                ?? this.normalizeId(result.clientId)
                ?? this.normalizeId(result?.context?.clientId)
                ?? this.normalizeId(result?.context?.clienteId)
                ?? this.normalizeId(result?.context?.id);

            const rawMode = typeof result.mode === 'string' ? result.mode.trim().toLowerCase() : '';
            const rawType = typeof result.type === 'string' ? result.type.trim().toLowerCase() : '';

            let targetType = rawType ? this.normalizeType(rawType) : null;

            if (!targetType) {
                if (rawMode === 'full') {
                    targetType = 'completo';
                } else if (rawMode === 'deferred' || rawMode === 'diferido') {
                    targetType = 'diferido';
                }
            }

            return this.applyCalculatorSubmission({
                clienteId: clientId,
                tipo: targetType ?? undefined,
                monto: result.amount ?? result.monto,
                cliente,
            });
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
            const tolerance = 0.005;
            const pagos = this.clients
                .map((client) => {
                    const pagoId = this.normalizeId(client.pago_proyectado_id);
                    if (pagoId === null) {
                        return [];
                    }

                    const amount = this.normalizeNumber(client.monto);
                    const monto = amount !== null ? Math.max(amount, 0) : 0;
                    const entries = [
                        {
                            pago_proyectado_id: pagoId,
                            tipo: this.normalizeType(client.tipo),
                            monto,
                        },
                    ];

                    const anticipoAmount = this.normalizeNumber(client.anticipo);
                    if (anticipoAmount !== null && anticipoAmount > tolerance) {
                        entries.push({
                            pago_proyectado_id: pagoId,
                            tipo: 'anticipo',
                            monto: Math.max(anticipoAmount, 0),
                        });
                    }

                    return entries;
                })
                .flat();

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
