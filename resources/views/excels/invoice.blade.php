<table>
    <thead>
        <tr>
            <th>Transactions - Date : {{ $date ?? 'All Dates' }}</th>
        </tr>
    </thead>
</table>
<table style="border-collapse: collapse; width: 100%;">
    <thead>
        <tr>
            <th style="border: 1px solid #000000; padding: 4px;">Transaction #</th>
            <th style="border: 1px solid #000000; padding: 4px;">Date</th>
            <th style="border: 1px solid #000000; padding: 4px;">Items</th>
            <th style="border: 1px solid #000000; padding: 4px;">Quantity</th>
            <th style="border: 1px solid #000000; padding: 4px;">Subtotal</th>
            <th style="border: 1px solid #000000; padding: 4px;">Discount</th>
            <th style="border: 1px solid #000000; padding: 4px;">Tax</th>
            <th style="border: 1px solid #000000; padding: 4px;">Total</th>
            <th style="border: 1px solid #000000; padding: 4px;">Cash</th>
            <th style="border: 1px solid #000000; padding: 4px;">Change</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($invoices as $invoice)
            @php
                $items = is_array($invoice->items) ? $invoice->items : [];
                $itemCount = count($items);
                $lastIndex = $itemCount - 1;
            @endphp

            @foreach ($items as $index => $item)
                <tr>
                    @if ($index == 0)
                        <!-- First row: Include all transaction-level cells with rowspan -->
                        <td style="border: 1px solid #000000; padding: 4px; text-align:center; vertical-align:top;{{ $index == $lastIndex ? ' border-bottom: 1px solid #000000;' : '' }}"
                            rowspan="{{ $itemCount }}">
                            {{ str_pad($invoice->id, 5, '0', STR_PAD_LEFT) }}
                        </td>
                        <td style="border: 1px solid #000000; padding: 4px; vertical-align:top;{{ $index == $lastIndex ? ' border-bottom: 1px solid #000000;' : '' }}"
                            rowspan="{{ $itemCount }}">
                            {{ $invoice->created_at->format('M d, Y') }}
                        </td>
                    @endif

                    <!-- Item details for each row (always included) -->
                    <td
                        style="border: 1px solid #000000; padding: 4px;{{ $index == $lastIndex ? ' border-bottom: 1px solid #000000;' : '' }}">
                        {{ $item['product_name'] }}
                    </td>
                    <td
                        style="border: 1px solid #000000; padding: 4px; text-align: center;{{ $index == $lastIndex ? ' border-bottom: 1px solid #000000;' : '' }}">
                        x{{ $item['qty'] }}
                    </td>

                    @if ($index == 0)
                        <!-- First row: Include all transaction-level numeric cells with rowspan -->
                        <td style="border: 1px solid #000000; padding: 4px; text-align:right; vertical-align:top;{{ $index == $lastIndex ? ' border-bottom: 1px solid #000000;' : '' }}"
                            rowspan="{{ $itemCount }}">
                            ₱{{ number_format($invoice->subtotal, 2) }}
                        </td>
                        <td style="border: 1px solid #000000; padding: 4px; text-align:right; vertical-align:top;{{ $index == $lastIndex ? ' border-bottom: 1px solid #000000;' : '' }}"
                            rowspan="{{ $itemCount }}">
                            ₱{{ number_format($invoice->discount, 2) }}
                        </td>
                        <td style="border: 1px solid #000000; padding: 4px; text-align:right; vertical-align:top;{{ $index == $lastIndex ? ' border-bottom: 1px solid #000000;' : '' }}"
                            rowspan="{{ $itemCount }}">
                            ₱{{ number_format($invoice->tax, 2) }}
                        </td>
                        <td style="border: 1px solid #000000; padding: 4px; text-align:right; vertical-align:top;{{ $index == $lastIndex ? ' border-bottom: 1px solid #000000;' : '' }}"
                            rowspan="{{ $itemCount }}">
                            ₱{{ number_format($invoice->total, 2) }}
                        </td>
                        <td style="border: 1px solid #000000; padding: 4px; text-align:right; vertical-align:top;{{ $index == $lastIndex ? ' border-bottom: 1px solid #000000;' : '' }}"
                            rowspan="{{ $itemCount }}">
                            ₱{{ number_format($invoice->cash, 2) }}
                        </td>
                        <td style="border: 1px solid #000000; padding: 4px; text-align:right; vertical-align:top;{{ $index == $lastIndex ? ' border-bottom: 1px solid #000000;' : '' }}"
                            rowspan="{{ $itemCount }}">
                            ₱{{ number_format($invoice->change, 2) }}
                        </td>
                    @else
                        <!-- Subsequent rows: Fill empty cells for the transaction-level columns that are spanned -->
                        <!-- These cells are "consumed" by the rowspan, so we don't output them -->
                        <!-- The browser automatically handles the spanning -->
                    @endif
                </tr>
            @endforeach
        @endforeach
    </tbody>
</table>
