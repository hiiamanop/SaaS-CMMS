import './bootstrap';
import QRCode from 'qrcode';
import Alpine from 'alpinejs';

window.QRCode = QRCode;
window.Alpine = Alpine;

// Helper to print standard industrial asset/inventory QR label
window.printQrLabel = function({ title, code, category, location, qrValue }) {
    QRCode.toDataURL(qrValue || code, { width: 180, margin: 1 }, function (err, url) {
        if (err) {
            alert('Gagal menghasilkan QR code');
            return;
        }
        const win = window.open('', '_blank', 'width=450,height=550');
        win.document.write(`
            <!DOCTYPE html>
            <html>
            <head>
                <title>Label QR - ${code}</title>
                <style>
                    @page { size: auto; margin: 5mm; }
                    body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; margin: 0; padding: 15px; display: flex; justify-content: center; }
                    .label-card {
                        width: 320px;
                        border: 2px solid #1f2937;
                        border-radius: 12px;
                        padding: 16px;
                        background: #fff;
                        box-sizing: border-box;
                    }
                    .header {
                        display: flex;
                        align-items: center;
                        border-bottom: 2px solid #e5e7eb;
                        padding-bottom: 8px;
                        margin-bottom: 12px;
                    }
                    .logo-text { font-weight: 900; font-size: 14px; color: #111827; }
                    .sub-logo { font-size: 8px; color: #6b7280; font-weight: bold; letter-spacing: 0.5px; }
                    .qr-container { text-align: center; margin: 10px 0; }
                    .qr-img { width: 140px; height: 140px; }
                    .code-badge {
                        font-family: monospace;
                        font-size: 13px;
                        font-weight: bold;
                        background: #f3f4f6;
                        padding: 4px 8px;
                        border-radius: 6px;
                        display: inline-block;
                        margin-top: 4px;
                    }
                    .info-row { margin-top: 8px; font-size: 12px; }
                    .info-title { font-weight: bold; color: #111827; font-size: 14px; margin-bottom: 4px; }
                    .info-meta { color: #4b5563; display: flex; justify-content: space-between; font-size: 11px; }
                    @media print {
                        body { padding: 0; }
                        .no-print { display: none; }
                    }
                </style>
            </head>
            <body>
                <div class="label-card">
                    <div class="header">
                        <div>
                            <div class="logo-text">ARUNA CMMS</div>
                            <div class="sub-logo">PT ARUNA HIJAU POWER</div>
                        </div>
                    </div>
                    <div class="info-title">${title}</div>
                    <div class="qr-container">
                        <img src="${url}" class="qr-img" alt="QR Code" />
                        <div><span class="code-badge">${code}</span></div>
                    </div>
                    <div class="info-row">
                        <div class="info-meta">
                            <span><strong>Kategori:</strong> ${category || '—'}</span>
                            <span><strong>Lokasi:</strong> ${location || '—'}</span>
                        </div>
                    </div>
                </div>
                <script>
                    window.onload = function() {
                        window.print();
                    };
                <\/script>
            </body>
            </html>
        `);
        win.document.close();
    });
};

Alpine.start();
