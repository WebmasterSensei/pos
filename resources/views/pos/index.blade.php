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
            {{-- <div class="product-grid-wrap">
            <div class="product-grid" id="productGrid">
                @forelse($products as $product)
                    <div class="product-card {{ $product['stock'] <= 0 ? 'out-of-stock' : '' }}"
                         data-id="{{ $product['id'] }}"
                         data-name="{{ $product['name'] }}"
                         data-price="{{ $product['price'] }}"
                         data-stock="{{ $product['stock'] }}"
                         data-category="{{ $product['category'] }}"
                         data-barcode="{{ $product['barcode'] }}"
                         data-emoji="{{ $product['emoji'] }}"
                         onclick="{{ $product['stock'] > 0 ? 'addToCart(this)' : '' }}"
                    >
                        <div class="product-emoji">{{ $product['emoji'] }}</div>
                        <div class="product-name">{{ $product['name'] }}</div>
                        <div class="product-price">₱{{ number_format($product['price'], 2) }}</div>
                        <div class="product-stock">
                            @if ($product['stock'] <= 0)
                                <span class="badge badge-red">OUT OF STOCK</span>
                            @elseif($product['stock'] <= 5)
                                <span class="badge badge-amber">{{ $product['stock'] }} left</span>
                            @else
                                <span class="badge badge-green">{{ $product['stock'] }} in stock</span>
                            @endif
                        </div>
                    </div>
                @empty
                    <div style="grid-column:1/-1;text-align:center;padding:40px;color:var(--text-muted);font-family:var(--font-mono);font-size:12px;">
                        No products found. <a href="{{ route('inventory.index') }}" style="color:var(--accent)">Add inventory →</a>
                    </div>
                @endforelse
            </div>
        </div> --}}
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
                    <button class="btn btn-secondary btn-sm" onclick="clearCart()">✕ Clear</button>
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
            <div style="text-align:center;margin-bottom:16px;">
                <div style="font-size:36px;margin-bottom:8px;">✓</div>
                <div class="modal-title" style="text-align:center;color:var(--green)">Payment Successful</div>
                <div style="font-family:var(--font-mono);font-size:10px;color:var(--text-muted);letter-spacing:0.1em">NEXUS
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

        function clearCart() {
            if (cart.length === 0) return;
            cart = [];
            discount = {
                type: 'percent',
                val: 0
            };
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
                <div class="cart-item-price">₱${item.price.toFixed(2)} each</div>
            </div>
            <div class="cart-item-controls">
                <button class="qty-btn" onclick="changeQty('${item.id}', -1)">−</button>
                <span class="qty-val">${item.qty}</span>
                <button class="qty-btn" onclick="changeQty('${item.id}', 1)">+</button>
            </div>
            <div class="cart-item-total">₱${(item.price * item.qty).toFixed(2)}</div>
            <button class="btn btn-icon btn-danger btn-sm" onclick="removeItem('${item.id}')" style="margin-left:4px">✕</button>
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
            <span>${i.emoji} ${i.name} ×${i.qty}</span>
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
            document.getElementById('receiptModal').classList.remove('open');
            clearCart();
            document.getElementById('cashInput').value = '';
            barcodeInput.focus();
        }

        function printReceipt() {
            window.print();
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

        function applyDiscountValue() {
            const type = document.getElementById('discountType').value;
            const val = parseFloat(document.getElementById('discountAmt').value) || 0;
            discount = {
                type,
                val
            };
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
