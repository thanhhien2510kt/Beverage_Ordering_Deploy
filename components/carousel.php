<?php
if (!isset($images) || empty($images)) {
    return;
}

$carouselId = $carouselId ?? 'hero-carousel';
$totalImages = count($images);
?>

<div class="carousel-container" id="<?php echo htmlspecialchars($carouselId); ?>">
    <div class="carousel-wrapper">
        <div class="carousel-slides" style="width: <?php echo $totalImages * 100; ?>%;">
            <?php foreach ($images as $index => $image): ?>
                <div class="carousel-slide" style="width: <?php echo 100 / $totalImages; ?>%;">
                    <img
                        src="<?php echo htmlspecialchars($image); ?>"
                        alt="Slide <?php echo $index + 1; ?>"
                        <?php echo $index === 0 ? 'loading="eager" fetchpriority="high"' : 'loading="lazy"'; ?>
                        decoding="async"
                    >
                </div>
            <?php endforeach; ?>
        </div>
        
        <!-- Navigation Buttons -->
        <button class="carousel-btn carousel-prev" aria-label="Previous">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <path d="M15 18l-6-6 6-6"/>
            </svg>
        </button>
        <button class="carousel-btn carousel-next" aria-label="Next">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <path d="M9 18l6-6-6-6"/>
            </svg>
        </button>
    </div>
    
    <!-- Pagination Dots -->
    <div class="carousel-pagination">
        <?php foreach ($images as $index => $image): ?>
            <button class="carousel-dot <?php echo $index === 0 ? 'active' : ''; ?>" data-index="<?php echo $index; ?>"></button>
        <?php endforeach; ?>
    </div>
</div>

<script>
(function () {
    const carousel = document.getElementById('<?php echo htmlspecialchars($carouselId); ?>');
    if (!carousel) return;

    const wrapper        = carousel.querySelector('.carousel-wrapper');
    const slidesEl       = carousel.querySelector('.carousel-slides');
    const dots           = carousel.querySelectorAll('.carousel-dot');
    const prevBtn        = carousel.querySelector('.carousel-prev');
    const nextBtn        = carousel.querySelector('.carousel-next');
    const TOTAL          = <?php echo $totalImages; ?>;
    const TRANSITION_MS  = 450;   // must match CSS transition duration
    const AUTOPLAY_DELAY = 4000;

    let current    = 0;
    let isAnimating = false;
    let timer      = null;

    /* ── Height: fill viewport minus header ── */
    function setHeight() {
        const header = document.querySelector('.main-header');
        wrapper.style.height = (window.innerHeight - (header ? header.offsetHeight : 0)) + 'px';
    }

    /* Debounced resize so it doesn't fire 60×/s */
    let resizeRaf;
    window.addEventListener('resize', () => {
        cancelAnimationFrame(resizeRaf);
        resizeRaf = requestAnimationFrame(setHeight);
    });

    setHeight();

    /* ── Slide engine ── */
    function goTo(index) {
        if (isAnimating) return;
        if (index < 0)      index = TOTAL - 1;
        if (index >= TOTAL) index = 0;

        isAnimating = true;
        current = index;

        /* translate3d triggers GPU compositing layer */
        const pct = -(index * 100 / TOTAL);
        slidesEl.style.transform = 'translate3d(' + pct + '%, 0, 0)';

        dots.forEach((d, i) => d.classList.toggle('active', i === index));

        setTimeout(() => { isAnimating = false; }, TRANSITION_MS);
    }

    /* ── Autoplay ── */
    function startAutoPlay() {
        clearInterval(timer);
        timer = setInterval(() => goTo(current + 1), AUTOPLAY_DELAY);
    }
    function stopAutoPlay() { clearInterval(timer); }

    /* ── Button controls ── */
    prevBtn.addEventListener('click', () => { goTo(current - 1); startAutoPlay(); });
    nextBtn.addEventListener('click', () => { goTo(current + 1); startAutoPlay(); });

    dots.forEach((dot, i) => dot.addEventListener('click', () => { goTo(i); startAutoPlay(); }));

    /* ── Pause on hover ── */
    carousel.addEventListener('mouseenter', stopAutoPlay);
    carousel.addEventListener('mouseleave', startAutoPlay);

    /* ── Touch / swipe support ── */
    let touchStartX = 0;
    let touchStartY = 0;
    let isDragging  = false;

    carousel.addEventListener('touchstart', (e) => {
        touchStartX = e.touches[0].clientX;
        touchStartY = e.touches[0].clientY;
        isDragging  = true;
    }, { passive: true });

    carousel.addEventListener('touchmove', (e) => {
        if (!isDragging) return;
        const dx = e.touches[0].clientX - touchStartX;
        const dy = e.touches[0].clientY - touchStartY;
        /* Only prevent scroll if primarily horizontal swipe */
        if (Math.abs(dx) > Math.abs(dy)) e.preventDefault();
    }, { passive: false });

    carousel.addEventListener('touchend', (e) => {
        if (!isDragging) return;
        isDragging = false;
        const dx = e.changedTouches[0].clientX - touchStartX;
        if (Math.abs(dx) > 50) {          // 50px threshold
            goTo(dx < 0 ? current + 1 : current - 1);
            startAutoPlay();
        }
    }, { passive: true });

    /* ── Keyboard accessibility ── */
    carousel.setAttribute('tabindex', '0');
    carousel.addEventListener('keydown', (e) => {
        if (e.key === 'ArrowLeft')  { goTo(current - 1); startAutoPlay(); }
        if (e.key === 'ArrowRight') { goTo(current + 1); startAutoPlay(); }
    });

    /* ── Init ── */
    goTo(0);
    startAutoPlay();
})();
</script>
