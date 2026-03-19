@extends('layouts.app')

@section('title', 'Register — NEXUS POS')

@section('content')
    <div class="pos-layout">

        <!-- LEFT: Product Browser -->
        <div class="pos-left">

            <!-- Scanner Bar -->
            <div class="scanner-bar" id="scannerBar">
                <div class="scanner-input-wrap">
                    <span class="scanner-icon">⌗</span>
                    <input type="text" id="barcodeInput" class="scanner-input"
                        placeholder="Scan barcode or type to search..." autocomplete="off" autofocus data-scanning="true">
                    <div id="scanStatus" class="scan-indicator">
                        ◉ READY TO SCAN
                    </div>
                </div>
                <div class="scanner-status">
                    <span id="lastScanned">Last scan: —</span>
                </div>
            </div>

            <!-- Category Pills -->
            <div class="category-pills" id="categoryPills">
                <button class="pill active" data-category="all" onclick="filterCategory(this, 'all')">All Items</button>
                @foreach ($categories as $cat)
                    <button class="pill" data-category="{{ $cat->category_name }}"
                        onclick="filterCategory(this, '{{ $cat->id }}')">{{ $cat->category_name }}</button>
                @endforeach
            </div>

            <!-- Product Search -->
            <div class="product-search-bar">
                <input type="text" id="productSearch" class="form-control" placeholder="Search products by name..."
                    oninput="filterProducts(this.value)" style="flex:1">
            </div>

            <!-- Product Grid -->
            <div class="product-grid-wrap">
                <div class="product-grid" id="productGrid">
                    @foreach ($products as $item)
                        {{-- {{$item}} --}}
                        <div class="cardindex" onclick='selectProduct(@json($item))'>
                            <div class="one">
                                <span class="title">{{ $item->category_name }}</span>
                                <div class="music">
                                    <img style="height: 80px; width: 80px; border-radius: 20px" src="{{ $item->images }}"
                                        alt="">
                                </div>
                                <span class="name">
                                    <div></div>
                                    {{ $item->product_name }}
                                </span>
                                <span class="name1 title">
                                    <div></div>
                                    ₱ {{ number_format($item->price, 2) }}
                                </span>
                                <div class="bar">

                                </div>
                                <div class="bar">
                                    <svg viewBox="0 0 16 16" class="color1 bi bi-suit-heart" fill="currentColor"
                                        height="14" width="14" xmlns="http://www.w3.org/2000/svg">
                                        <path
                                            d="m8 6.236-.894-1.789c-.222-.443-.607-1.08-1.152-1.595C5.418 2.345 4.776 2 4 2 2.324 2 1 3.326 1 4.92c0 1.211.554 2.066 1.868 3.37.337.334.721.695 1.146 1.093C5.122 10.423 6.5 11.717 8 13.447c1.5-1.73 2.878-3.024 3.986-4.064.425-.398.81-.76 1.146-1.093C14.446 6.986 15 6.131 15 4.92 15 3.326 13.676 2 12 2c-.777 0-1.418.345-1.954.852-.545.515-.93 1.152-1.152 1.595L8 6.236zm.392 8.292a.513.513 0 0 1-.784 0c-1.601-1.902-3.05-3.262-4.243-4.381C1.3 8.208 0 6.989 0 4.92 0 2.755 1.79 1 4 1c1.6 0 2.719 1.05 3.404 2.008.26.365.458.716.596.992a7.55 7.55 0 0 1 .596-.992C9.281 2.049 10.4 1 12 1c2.21 0 4 1.755 4 3.92 0 2.069-1.3 3.288-3.365 5.227-1.193 1.12-2.642 2.48-4.243 4.38z">
                                        </path>
                                    </svg>
                                    <svg viewBox="0 0 16 16" class="color1 bi bi-arrow-right" fill="currentColor"
                                        height="14" width="14" xmlns="http://www.w3.org/2000/svg">
                                        <path
                                            d="M1 8a.5.5 0 0 1 .5-.5h11.793l-3.147-3.146a.5.5 0 0 1 .708-.708l4 4a.5.5 0 0 1 0 .708l-4 4a.5.5 0 0 1-.708-.708L13.293 8.5H1.5A.5.5 0 0 1 1 8z"
                                            fill-rule="evenodd"></path>
                                    </svg>
                                </div>
                            </div>
                            <div class="two"></div>
                            <div class="three"></div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- RIGHT: Cart Panel -->
        <div class="pos-right">

            <!-- Cart Header -->
            <div class="cart-header">
                <div class="cart-title">
                    Cart
                    <span class="cart-count" id="cartCount">0 items</span>
                </div>
                <div style="display:flex;gap:8px;margin-top:12px;">
                    <button class="btn btn-secondary btn-sm" onclick="clearCart()">✕ Clear All</button>
                    <button class="btn btn-secondary btn-sm" onclick="applyDiscount()">% Discount</button>
                </div>
            </div>

            <!-- Cart Items -->
            <div class="cart-items" id="cartItems">
                <div class="cart-empty" id="cartEmpty">
                    <div class="cart-empty-icon">🛒</div>
                    <span>CART IS EMPTY</span>
                    <span style="color:var(--text-muted);font-size:10px">Scan or click products to add</span>
                </div>
            </div>

            <!-- Cart Footer -->
            <div class="cart-footer">
                <div class="cart-totals">
                    <div class="total-row">
                        <span class="total-label">Subtotal</span>
                        <span class="total-val" id="subtotal">₱0.00</span>
                    </div>
                    <div class="total-row">
                        <span class="total-label">Discount</span>
                        <span class="total-val" id="discountDisplay">₱0.00</span>
                    </div>
                    <div class="total-row">
                        <span class="total-label">Tax (12%)</span>
                        <span class="total-val" id="taxDisplay">₱0.00</span>
                    </div>
                    <div class="total-row grand">
                        <span class="total-label">TOTAL</span>
                        <span class="total-val" id="grandTotal">₱0.00</span>
                    </div>
                </div>

                <div class="payment-section">
                    <div class="cash-input-row">
                        <div class="cash-prefix">₱</div>
                        <input type="number" id="cashInput" class="cash-input" placeholder="0.00" step="0.01"
                            oninput="calcChange()">
                    </div>
                    <div class="change-display">
                        <span class="change-label">Change</span>
                        <span class="change-val" id="changeDisplay">₱0.00</span>
                    </div>
                    <button class="btn btn-success" onclick="checkout()">
                        ✦ Process Payment
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Receipt Modal -->
    <div class="modal-overlay" id="receiptModal">
        <div class="modal">
            <div id="printableArea">
                <div style="text-align:center;margin-bottom:16px;">
                    <div style="font-size:36px;margin-bottom:8px;">✓</div>
                    <div class="modal-title" style="text-align:center;color:var(--green)">Payment Successful</div>
                    <div style="font-family:var(--font-mono);font-size:10px;color:var(--text-muted);letter-spacing:0.1em">
                        NEXUS
                        POS · RECEIPT</div>
                </div>
                <hr class="receipt-divider">
                <div id="receiptItems"></div>
                <hr class="receipt-divider">
                <div class="receipt-row total">
                    <span>TOTAL PAID</span>
                    <span id="receiptTotal"></span>
                </div>
                <div class="receipt-row">
                    <span>CASH</span>
                    <span id="receiptCash"></span>
                </div>
                <div class="receipt-row" style="color:var(--green)">
                    <span>CHANGE</span>
                    <span id="receiptChange"></span>
                </div>
                <hr class="receipt-divider">
                <div
                    style="text-align:center;font-family:var(--font-mono);font-size:9px;color:var(--text-muted);letter-spacing:0.06em;margin-bottom:16px;">
                    THANK YOU FOR YOUR PURCHASE
                </div>
            </div>
            <div class="modal-actions">
                <button class="btn btn-secondary" style="flex:1" onclick="printReceipt()">⎙ Print</button>
                <button class="btn btn-primary" style="flex:1" onclick="closeReceipt()">New Sale ›</button>
            </div>
        </div>
    </div>

    <!-- Discount Modal -->
    <div class="modal-overlay" id="discountModal">
        <div class="modal">
            <div class="modal-title">Apply Discount</div>
            <div class="form-group">
                <label class="form-label">Discount Type</label>
                <select class="form-control" id="discountType" onchange="updateDiscountLabel()">
                    <option value="percent">Percentage (%)</option>
                    <option value="fixed">Fixed Amount (₱)</option>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label" id="discountAmtLabel">Discount (%)</label>
                <input type="number" class="form-control" id="discountAmt" placeholder="0" min="0"
                    max="100">
            </div>
            <div class="modal-actions">
                <button class="btn btn-secondary" style="flex:1" onclick="closeDiscount()">Cancel</button>
                <button class="btn btn-primary" style="flex:1" onclick="applyDiscountValue()">Apply</button>
            </div>
        </div>
    </div>

    <!-- Toast Container -->
    <div class="toast-container" id="toastContainer"></div>
@endsection

@push('scripts')
    <script>
        // ===== STATE =====
        let cart = [];
        let discount = {
            type: 'percent',
            val: 0
        };
        const TAX_RATE = 0.12;
        let scanBuffer = '';
        let scanTimer = null;

        // ===== BARCODE SCANNER =====
        const barcodeInput = document.getElementById('barcodeInput');

        barcodeInput.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                const code = this.value.trim();
                if (code) {
                    processBarcode(code);
                    this.value = '';
                }
            }
        });

        // Auto-focus scanner (hardware scanners type fast and send Enter)
        document.addEventListener('keydown', function(e) {
            // If focused on another input, skip
            if (document.activeElement.tagName === 'INPUT' && document.activeElement !== barcodeInput) return;

            // Refocus barcode input on any key press if not in a form
            if (e.key.length === 1 || e.key === 'Enter') {
                if (document.activeElement !== barcodeInput) {
                    barcodeInput.focus();
                }
            }
        });

        function processBarcode(code) {
            document.getElementById('lastScanned').textContent = `Last scan: ${code}`;
            barcodeInput.classList.add('scan-flash');
            setTimeout(() => barcodeInput.classList.remove('scan-flash'), 500);

            fetch('scanning-barcode', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        code: code
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        addToCart(data.product);
                        showToast(`✓ ${data.message}`, 'success');
                    } else {
                        showToast(`✕ ${data.message}`, 'error');
                    }
                })
                .catch(err => {
                    showToast(`✕ Error scanning barcode`, 'error');
                    console.error(err);
                });
        }
        // ===== PRODUCT FILTER =====
        function filterCategory(el, cat) {
            document.querySelectorAll('.pill').forEach(p => p.classList.remove('active'));
            el.classList.add('active');
            const cards = document.querySelectorAll('.product-card');
            cards.forEach(c => {
                const show = cat === 'all' || c.dataset.category === cat;
                c.style.display = show ? '' : 'none';
            });
        }

        function filterProducts(query) {
            const q = query.toLowerCase();
            document.querySelectorAll('.product-card').forEach(c => {
                const match = c.dataset.name.toLowerCase().includes(q) ||
                    c.dataset.barcode.toLowerCase().includes(q);
                c.style.display = match ? '' : 'none';
            });
        }

        // ===== CART LOGIC =====
        function addToCart(card) {
            const id = card.id;
            const existing = cart.find(i => i.id === id);
            const maxStock = parseInt(card.stock);

            if (existing) {
                if (existing.qty >= maxStock) {
                    showToast('✕ Not enough stock', 'error');
                    return;
                }
                existing.qty++;
            } else {
                cart.push({
                    id,
                    qty: 1,
                    name: card.product_name,
                    price: parseFloat(card.price),
                    stock: maxStock,
                    image: card.images
                });
            }

            // Save updated cart to localStorage
            localStorage.setItem('cart', JSON.stringify(cart));

            renderCart();
        }

        function removeItem(id) {
            console.log(id)
            cart = cart.filter(i => i.id != id);
            // console.log(cart);
            localStorage.setItem('cart', JSON.stringify(cart));
            renderCart();
        }

        function changeQty(id, delta) {
            const item = cart.find(i => i.id === id);
            if (!item) return;

            const newQty = item.qty + delta;
            if (newQty > item.stock) {
                showToast('✕ Not enough stock', 'error');
                return;
            } else if (newQty <= 0) {
                cart = cart.filter(i => i.id !== id);
            } else {
                item.qty = newQty;
            }

            localStorage.setItem('cart', JSON.stringify(cart));
            renderCart();
        }

        function selectProduct(card) {
            const id = card.id;
            const existing = cart.find(i => i.id === id);
            const maxStock = parseInt(card.stock);

            if (existing) {
                if (existing.qty >= maxStock) {
                    showToast('✕ Not enough stock', 'error');
                    return;
                }
                existing.qty++;
            } else {
                cart.push({
                    id,
                    qty: 1,
                    name: card.product_name,
                    price: parseFloat(card.price),
                    stock: maxStock,
                    image: card.images
                });
            }

            // Save updated cart to localStorage
            localStorage.setItem('cart', JSON.stringify(cart));

            renderCart();
        }

        function clearCart() {
            if (cart.length === 0) return;

            cart = [];
            discount = {
                type: 'percent',
                val: 0
            };

            localStorage.setItem('cart', JSON.stringify(cart)); // save empty cart
            localStorage.removeItem('discount');
            document.getElementById('cashInput').value = 0;
            updateTotals();
            renderCart();
        }
        document.addEventListener('DOMContentLoaded', () => {
            const savedCart = localStorage.getItem('cart');
            if (savedCart) {
                cart = JSON.parse(savedCart);
                renderCart();
            }
        });

        function renderCart() {
            const container = document.getElementById('cartItems');
            const empty = document.getElementById('cartEmpty');
            const count = cart.reduce((s, i) => s + i.qty, 0);

            document.getElementById('cartCount').textContent = `${count} item${count !== 1 ? 's' : ''}`;

            if (cart.length === 0) {
                container.innerHTML = '';
                container.appendChild(empty);
                empty.style.display = 'flex';
                updateTotals();
                return;
            }
            // empty.style.display = 'none';
            if (Array.isArray(cart) && cart.length > 0) {
                container.innerHTML = cart.map(item => `
        <div class="cart-item" id="cart-${item.id}">
            <img src="${item.image || '/storage/products/productsimage.png'}" style="height: 20px; width: 20px;" alt="${item.name}">
            <div class="cart-item-info">
                <div class="cart-item-name">${item.name}</div>
               <div class="cart-item-price">₱${item.price.toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</div>
            </div>
            <div class="cart-item-controls">
                <button class="qty-btn" onclick="changeQty('${item.id}', -1)">−</button>
                <span class="qty-val">${item.qty}</span>
                <button class="qty-btn" onclick="changeQty('${item.id}', 1)">+</button>
            </div>
            <div class="cart-item-total">₱${(item.price * item.qty).toFixed(2)}</div>
            <button class="btn btn-icon btn-sm" onclick="removeItem('${item.id}')" style="margin-left:4px">✕</button>
        </div>
    `).join('');
            } else {
                container.innerHTML = '<p>Your cart is empty</p>';
            }

            updateTotals();
        }

        function updateTotals() {
            const subtotal = cart.reduce((s, i) => s + i.price * i.qty, 0);
            let discountAmt = discount.type === 'percent' ?
                subtotal * (discount.val / 100) :
                Math.min(discount.val, subtotal);
            const taxable = subtotal - discountAmt;
            const tax = taxable * TAX_RATE;
            const total = taxable + tax;

            document.getElementById('subtotal').textContent = `₱${subtotal.toFixed(2)}`;
            document.getElementById('discountDisplay').textContent = `-₱${discountAmt.toFixed(2)}`;
            document.getElementById('taxDisplay').textContent = `₱${tax.toFixed(2)}`;
            document.getElementById('grandTotal').textContent = `₱${total.toFixed(2)}`;
            calcChange();
        }

        function calcChange() {
            const total = parseFloat(document.getElementById('grandTotal').textContent.replace('₱', '')) || 0;
            const cash = parseFloat(document.getElementById('cashInput').value) || 0;
            const change = Math.max(0, cash - total);
            document.getElementById('changeDisplay').textContent = `₱${change.toFixed(2)}`;
        }

        // ===== CHECKOUT =====
        function checkout() {
            if (cart.length === 0) {
                showToast('Cart is empty', 'error');
                return;
            }
            const total = parseFloat(document.getElementById('grandTotal').textContent.replace('₱', '')) || 0;
            const cash = parseFloat(document.getElementById('cashInput').value) || 0;
            if (cash < total) {
                showToast('Insufficient cash amount', 'error');
                return;
            }

            // Build receipt
            document.getElementById('receiptItems').innerHTML = cart.map(i => `
        <div class="receipt-row">
            <span><svg xmlns="http://www.w3.org/2000/svg" width="8" height="8" fill="currentColor" viewBox="0 0 16 16">
  <circle cx="2" cy="3" r="1"/>
  <circle cx="2" cy="8" r="1"/>
  <circle cx="2" cy="13" r="1"/>
  <rect x="5" y="2" width="9" height="2"/>
  <rect x="5" y="7" width="9" height="2"/>
  <rect x="5" y="12" width="9" height="2"/>
</svg> ${i.name} ×${i.qty}</span>
            <span>₱${(i.price * i.qty).toFixed(2)}</span>
        </div>
    `).join('');
            document.getElementById('receiptTotal').textContent = `₱${total.toFixed(2)}`;
            document.getElementById('receiptCash').textContent = `₱${cash.toFixed(2)}`;
            document.getElementById('receiptChange').textContent = `₱${(cash - total).toFixed(2)}`;

            // Submit transaction via AJAX
            fetch('{{ route('pos.checkout') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({
                    items: cart,
                    total,
                    cash,
                    change: cash - total,
                    discount
                })
            }).then(r => r.json()).then(data => {
                document.getElementById('receiptModal').classList.add('open');
            }).catch(() => {
                // Demo mode: still show receipt
                document.getElementById('receiptModal').classList.add('open');
            });
        }

        function closeReceipt() {
            const modal = document.getElementById('receiptModal').classList.remove('open'); // force hide
            clearCart();
            document.getElementById('cashInput').value = 0;
            barcodeInput.focus();
        }

        function printReceipt() {
            var printContents = document.getElementById('printableArea').innerHTML;
            var originalContents = document.body.innerHTML;

            var printWindow = window.open('', '', 'height=600,width=400');
            printWindow.document.write('<html><head><title>Receipt</title>');
            printWindow.document.write('<style>');
            printWindow.document.write('body { font-family: sans-serif; }'); // add any styles you need
            printWindow.document.write('.receipt-divider { border-top: 1px solid #ccc; margin: 8px 0; }');
            printWindow.document.write('.receipt-row { display: flex; justify-content: space-between; margin: 4px 0; }');
            printWindow.document.write('.total { font-weight: bold; }');
            printWindow.document.write('</style>');
            printWindow.document.write('</head><body>');
            printWindow.document.write(printContents);
            printWindow.document.write('</body></html>');

            printWindow.document.close();
            printWindow.focus();
            printWindow.print();
            printWindow.close();
        }
        // ===== DISCOUNT =====
        function applyDiscount() {
            document.getElementById('discountModal').classList.add('open');
        }

        function closeDiscount() {
            document.getElementById('discountModal').classList.remove('open');
        }

        function updateDiscountLabel() {
            const type = document.getElementById('discountType').value;
            document.getElementById('discountAmtLabel').textContent = type === 'percent' ? 'Discount (%)' :
                'Discount Amount (₱)';
        }

        const savedDiscount = localStorage.getItem('discount');
        if (savedDiscount) {
            discount = JSON.parse(savedDiscount);
        }

        function applyDiscountValue() {
            const type = document.getElementById('discountType').value;
            const val = parseFloat(document.getElementById('discountAmt').value) || 0;

            discount = {
                type,
                val
            };

            localStorage.setItem('discount', JSON.stringify(discount)); // persist

            closeDiscount();
            updateTotals();

            showToast(`✓ Discount applied: ${type === 'percent' ? val + '%' : '₱' + val}`, 'success');
        }
        // ===== TOAST =====
        function showToast(msg, type = 'success') {
            const container = document.getElementById('toastContainer');
            const toast = document.createElement('div');
            toast.className = `toast ${type}`;
            toast.innerHTML = `
        <span class="toast-icon">${type === 'success' ? '✓' : '✕'}</span>
        <span class="toast-msg">${msg}</span>
    `;
            container.appendChild(toast);
            setTimeout(() => {
                toast.style.opacity = '0';
                toast.style.transform = 'translateX(100%)';
                toast.style.transition = '0.3s';
                setTimeout(() => toast.remove(), 300);
            }, 2500);
        }

        // Initial render
        renderCart();
        barcodeInput.focus();
    </script>
@endpush
