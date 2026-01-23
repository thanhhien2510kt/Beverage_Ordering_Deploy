<?php
/**
 * Carousel Component - Simple slide transition
 * @param array $images - Array of image paths
 * @param string $carouselId - Unique carousel ID
 */
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
                    <img src="<?php echo htmlspecialchars($image); ?>" alt="Slide <?php echo $index + 1; ?>">
                </div>
            <?php endforeach; ?>
        </div>
        
        <!-- Navigation Buttons -->
        <button class="carousel-btn carousel-prev" aria-label="Previous">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M15 18l-6-6 6-6"/>
            </svg>
        </button>
        <button class="carousel-btn carousel-next" aria-label="Next">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
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
(function() {
    const carousel = document.getElementById('<?php echo htmlspecialchars($carouselId); ?>');
    if (!carousel) return;
    
    const slidesContainer = carousel.querySelector('.carousel-slides');
    const dots = carousel.querySelectorAll('.carousel-dot');
    const prevBtn = carousel.querySelector('.carousel-prev');
    const nextBtn = carousel.querySelector('.carousel-next');
    const totalSlides = <?php echo $totalImages; ?>;
    
    let currentIndex = 0;
    let autoPlayTimer = null;
    

    function setHeight() {
        const header = document.querySelector('.main-header');
        const headerHeight = header ? header.offsetHeight : 0;
        const height = window.innerHeight - headerHeight;
        carousel.querySelector('.carousel-wrapper').style.height = height + 'px';
    }
    
    setHeight();
    window.addEventListener('resize', setHeight);
    

    function showSlide(index) {
        if (index < 0) index = totalSlides - 1;
        if (index >= totalSlides) index = 0;
        
        currentIndex = index;
        slidesContainer.style.transform = 'translateX(-' + (index * 100 / totalSlides) + '%)';
        
        dots.forEach((dot, i) => {
            dot.classList.toggle('active', i === index);
        });
    }
    

    function startAutoPlay() {
        stopAutoPlay();
        autoPlayTimer = setInterval(() => {
            showSlide(currentIndex + 1);
        }, 3000); // 3 seconds per slide
    }
    
    function stopAutoPlay() {
        if (autoPlayTimer) {
            clearInterval(autoPlayTimer);
            autoPlayTimer = null;
        }
    }
    

    nextBtn.addEventListener('click', () => {
        showSlide(currentIndex + 1);
        startAutoPlay();
    });
    
    prevBtn.addEventListener('click', () => {
        showSlide(currentIndex - 1);
        startAutoPlay();
    });
    

    dots.forEach((dot, index) => {
        dot.addEventListener('click', () => {
            showSlide(index);
            startAutoPlay();
        });
    });
    

    carousel.addEventListener('mouseenter', stopAutoPlay);
    carousel.addEventListener('mouseleave', startAutoPlay);
    

    showSlide(0);
    startAutoPlay();
})();
</script>
