const slidesContainer = document.querySelector('.slides-container');
const slides = document.querySelectorAll('.slide');
const prevBtn = document.querySelector('.prev-btn');
const nextBtn = document.querySelector('.next-btn');

const indicators = document.querySelectorAll('.indicator');

let currentSlide = 0; 
const totalSlides = slides.length;

function updateCarousel() {
    slidesContainer.style.transform = `translateX(-${currentSlide * 100}%)`;
    indicators.forEach((indicator) => {
        indicator.classList.remove('active');
    });
    indicators[currentSlide].classList.add('active');
}

function nextSlide() {
    currentSlide++;

    if (currentSlide >= totalSlides) {
        currentSlide = 0;
    }
        updateCarousel();
}

function prevSlide() {
    currentSlide--;
    
    if (currentSlide < 0) {
        currentSlide = totalSlides - 1;
    }
    
    updateCarousel();
}

nextBtn.addEventListener('click', nextSlide);
prevBtn.addEventListener('click', prevSlide);

indicators.forEach((indicator, index) => {
    indicator.addEventListener('click', () => {
        currentSlide = index;
        updateCarousel();
    });
});

updateCarousel();