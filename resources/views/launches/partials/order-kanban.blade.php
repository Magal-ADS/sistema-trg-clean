<div data-order-kanban>
    <div data-kanban-message class="mb-3 hidden rounded-md px-4 py-3 text-sm font-semibold"></div>

    <div class="flex snap-x gap-4 overflow-x-auto pb-4">
        @foreach($statusLabels as $status => $label)
            @php($columnOrders = $orders->where('status', $status))
            <section data-kanban-column data-status="{{ $status }}" class="w-[280px] shrink-0 snap-start rounded-lg bg-slate-100 p-3 sm:w-[310px]">
                <header class="mb-3 flex items-center justify-between gap-2">
                    <h2 class="font-bold text-slate-800">{{ $label }}</h2>
                    <span data-kanban-count class="rounded-full bg-white px-2.5 py-1 text-xs font-bold text-slate-600">{{ $columnOrders->count() }}</span>
                </header>

                <div data-kanban-list class="min-h-32 space-y-3 rounded-md">
                    @foreach($columnOrders as $order)
                        <article
                            draggable="true"
                            data-kanban-card
                            data-status="{{ $order->status }}"
                            data-status-url="{{ route($statusRouteName, $order) }}"
                            class="cursor-grab rounded-md border border-slate-200 bg-white p-3 shadow-sm active:cursor-grabbing"
                        >
                            <div class="flex items-start justify-between gap-2">
                                <div class="min-w-0">
                                    <p class="truncate font-bold text-slate-950">{{ $order->code }}</p>
                                    <p class="truncate text-sm text-slate-600">{{ $order->customer_name }}</p>
                                </div>
                                <strong class="shrink-0 text-sm text-brand-primary">R$ {{ number_format((float) $order->total, 2, ',', '.') }}</strong>
                            </div>

                            @if($showCity)
                                <p class="mt-2 text-xs text-slate-500">{{ $order->city?->name ?: 'Sem cidade' }}</p>
                            @endif
                            <p class="mt-1 text-xs text-slate-500">
                                {{ $order->confirmed_at?->format('d/m/Y H:i') ?: $order->created_at?->format('d/m/Y H:i') }}
                                · {{ $order->items->sum('quantity') }} item(ns)
                            </p>

                            <label class="mt-3 block text-xs font-semibold text-slate-600">Status
                                <select data-kanban-status-select class="mt-1 h-9 w-full rounded-md border border-slate-300 bg-white px-2 text-xs outline-none focus:border-brand-secondary">
                                    @foreach($statusLabels as $optionStatus => $optionLabel)
                                        <option value="{{ $optionStatus }}" @selected($order->status === $optionStatus)>{{ $optionLabel }}</option>
                                    @endforeach
                                </select>
                            </label>

                            @if($showEdit)
                                <a href="{{ route('launches.admin.modules.edit', ['module' => 'orders', 'id' => $order->id]) }}" class="mt-3 inline-flex h-9 w-full items-center justify-center rounded-md border border-slate-200 text-xs font-bold text-brand-primary">Ver pedido</a>
                            @endif
                        </article>
                    @endforeach

                    <p data-kanban-empty class="{{ $columnOrders->isEmpty() ? '' : 'hidden' }} px-2 py-8 text-center text-xs text-slate-400">Arraste um pedido para esta coluna.</p>
                </div>
            </section>
        @endforeach
    </div>

    <p class="mt-1 text-xs text-slate-500">No celular, use o seletor de status em cada pedido.</p>
</div>

<script>
    (() => {
        const board = document.querySelector('[data-order-kanban]');

        if (!board) return;

        const csrfToken = @json(csrf_token());
        const message = board.querySelector('[data-kanban-message]');
        let draggedCard = null;

        const refreshColumns = () => {
            board.querySelectorAll('[data-kanban-column]').forEach((column) => {
                const cards = column.querySelectorAll('[data-kanban-card]');
                column.querySelector('[data-kanban-count]').textContent = cards.length;
                column.querySelector('[data-kanban-empty]').classList.toggle('hidden', cards.length > 0);
            });
        };

        const showMessage = (text, success) => {
            message.textContent = text;
            message.classList.remove('hidden', 'border-red-200', 'bg-red-50', 'text-red-700', 'border-emerald-200', 'bg-emerald-50', 'text-emerald-700');
            message.classList.add(...(success
                ? ['border', 'border-emerald-200', 'bg-emerald-50', 'text-emerald-700']
                : ['border', 'border-red-200', 'bg-red-50', 'text-red-700']));
        };

        const updateStatus = async (card, newStatus) => {
            const oldStatus = card.dataset.status;

            if (oldStatus === newStatus || card.dataset.saving === 'true') return;

            const oldList = card.closest('[data-kanban-list]');
            const newColumn = board.querySelector(`[data-kanban-column][data-status="${newStatus}"]`);
            const select = card.querySelector('[data-kanban-status-select]');

            if (!newColumn) return;

            card.dataset.saving = 'true';
            select.disabled = true;
            newColumn.querySelector('[data-kanban-list]').appendChild(card);
            card.dataset.status = newStatus;
            select.value = newStatus;
            refreshColumns();

            try {
                const response = await fetch(card.dataset.statusUrl, {
                    method: 'PATCH',
                    headers: {
                        Accept: 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                    },
                    body: JSON.stringify({ status: newStatus }),
                });

                if (!response.ok) throw new Error('status-update-failed');

                showMessage('Status do pedido atualizado.', true);
            } catch (error) {
                oldList.appendChild(card);
                card.dataset.status = oldStatus;
                select.value = oldStatus;
                refreshColumns();
                showMessage('Não foi possível atualizar o status. Tente novamente.', false);
            } finally {
                card.dataset.saving = 'false';
                select.disabled = false;
            }
        };

        board.querySelectorAll('[data-kanban-card]').forEach((card) => {
            card.addEventListener('dragstart', (event) => {
                if (event.target.closest('select, a, button')) {
                    event.preventDefault();
                    return;
                }

                draggedCard = card;
                card.classList.add('opacity-50');
                event.dataTransfer.effectAllowed = 'move';
            });

            card.addEventListener('dragend', () => {
                card.classList.remove('opacity-50');
                draggedCard = null;
            });

            card.querySelector('[data-kanban-status-select]').addEventListener('change', (event) => {
                updateStatus(card, event.target.value);
            });
        });

        board.querySelectorAll('[data-kanban-column]').forEach((column) => {
            column.addEventListener('dragover', (event) => {
                event.preventDefault();
                event.dataTransfer.dropEffect = 'move';
            });

            column.addEventListener('drop', (event) => {
                event.preventDefault();
                if (draggedCard) updateStatus(draggedCard, column.dataset.status);
            });
        });
    })();
</script>
