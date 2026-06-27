document.addEventListener("DOMContentLoaded", function () {
    // Bail if Swiper isn't loaded or the slider isn't on this page
    if (typeof Swiper === "undefined") return;
    if (!document.querySelector(".relatedCourseSlider")) return;

    new Swiper(".relatedCourseSlider", {
        slidesPerView: 3,
        spaceBetween: 30,
        loop: false,

       

        navigation: {
            nextEl: ".related-course-section .swiper-button-next",
            prevEl: ".related-course-section .swiper-button-prev",
        },

        breakpoints: {
            0: {
                slidesPerView: 1,
            },
            576: {
                slidesPerView: 2,
            },
            992: {
                slidesPerView: 3,
            },
        },
    });
});
