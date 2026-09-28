import Alpine from 'alpinejs';

window.Alpine = Alpine;

// Global helper formatting for IDR currency
Alpine.magic('money', () => {
    return (val) => new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0
    }).format(val || 0);
});

Alpine.start();
