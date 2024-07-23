const carouselInner = document.getElementById('carousel-inner');
        const prevButton = document.getElementById('prev');
        const nextButton = document.getElementById('next');
        let index = 0;
        const totalItems = document.querySelectorAll('.carousel-item').length;

        prevButton.addEventListener('click', () => {
            if (index > 0) {
                index--;
            } else {
                index = totalItems - 1;
            }
            carouselInner.style.transform = 'translateX(-' + index * 100 + '%)';
        });

        nextButton.addEventListener('click', () => {
            if (index < totalItems - 1) {
                index++;
            } else {
                index = 0;
            }
            carouselInner.style.transform = 'translateX(-' + index * 100 + '%)';
        });

        const autoSlide = () => {
            if (index < totalItems - 1) {
                index++;
            } else {
                index = 0;
            }
            carouselInner.style.transform = 'translateX(-' + index * 100 + '%)';
        }

        setInterval(autoSlide, 4000);
        // //desable right click
        // document.addEventListener('contextmenu', function(e) {
        //     e.preventDefault();
        // });
        document.addEventListener('keydown', function(event) {
            const SCROLL_AMOUNT = 50; // Change this value to adjust the scroll amount

            switch (event.key) {
                case "ArrowUp":
                    window.scrollBy(0, -SCROLL_AMOUNT);
                    break;
                case "ArrowDown":
                    window.scrollBy(0, SCROLL_AMOUNT);
                    break;
                case "ArrowLeft":
                    window.scrollBy(-SCROLL_AMOUNT, 0);
                    break;
                case "ArrowRight":
                    window.scrollBy(SCROLL_AMOUNT, 0);
                    break;
                default:
                    break;
            }
        });
        document.addEventListener("DOMContentLoaded", function() {
            var popup = document.getElementById("popup");
            var closePopup = document.getElementById("closePopup");
        
            // Delay showing the pop-up by 4 seconds
            setTimeout(function() {
                popup.style.display = "flex";
            }, 4000); // 4000 milliseconds = 4 seconds
        
            // Close the pop-up when the close button is clicked
            closePopup.addEventListener("click", function() {
                popup.style.display = "none";
            });
        });
                
        document.addEventListener('DOMContentLoaded', function() {
            const darkModeToggle = document.getElementById('darkModeToggle');
            const body = document.body;
        
            darkModeToggle.addEventListener('click', function() {
                body.classList.toggle('dark-mode');
        
                // Optionally, you can store user preference in localStorage
                const isDarkMode = body.classList.contains('dark-mode');
                localStorage.setItem('darkMode', isDarkMode);
            });
        
            // Check if dark mode preference is stored and apply it
            const isDarkMode = localStorage.getItem('darkMode') === 'true';
            if (isDarkMode) {
                body.classList.add('dark-mode');
            }
        });
        
           