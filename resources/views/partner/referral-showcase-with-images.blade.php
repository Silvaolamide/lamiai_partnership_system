@include('partner.referral-showcase')

<script>
window.addEventListener('DOMContentLoaded', function () {
    const products = @json($products->map(function ($product) {
        return [
            'name' => $product->name,
            'image' => $product->featured_image ? Storage::url($product->featured_image) : null,
        ];
    })->values());

    const cards = document.querySelectorAll('#products .grid > article');

    cards.forEach(function (card, index) {
        const product = products[index];
        if (!product || !product.image) {
            return;
        }

        const cover = card.firstElementChild;
        if (!cover) {
            return;
        }

        cover.className = 'relative h-56 w-full overflow-hidden bg-slate-100';
        cover.innerHTML = '';

        const image = document.createElement('img');
        image.src = product.image;
        image.alt = product.name;
        image.className = 'h-full w-full object-cover transition duration-500 group-hover:scale-105';
        image.loading = 'lazy';

        cover.appendChild(image);
    });
});
</script>
