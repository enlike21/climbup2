// Toggle between grid and list view
document.addEventListener('DOMContentLoaded', function() {
    const viewButtons = document.querySelectorAll('.view-btn');
    const routesContainer = document.querySelector('.routes-container');

    // Set the view from localStorage or default to grid
    const savedView = localStorage.getItem('routesView') || 'grid';
    routesContainer.className = `routes-container ${savedView}-view`;

    // Set active button
    viewButtons.forEach(button => {
        if (button.dataset.view === savedView) {
            button.classList.add('active');
        } else {
            button.classList.remove('active');
        }

        // Add click event
        button.addEventListener('click', function() {
            const view = this.dataset.view;

            // Update container class
            routesContainer.className = `routes-container ${view}-view`;

            // Update active button
            viewButtons.forEach(btn => btn.classList.remove('active'));
            this.classList.add('active');

            // Save preference
            localStorage.setItem('routesView', view);
        });
    });

    // Handle save route button
    const saveButtons = document.querySelectorAll('.save-route-btn');
    saveButtons.forEach(button => {
        button.addEventListener('click', function() {
            const routeId = this.dataset.routeId;
            const routeCard = this.closest('.route-card');
            const successMessage = routeCard.querySelector('.success-message');

            // Get the save route URL from the data attribute
            const saveRouteUrl = document.getElementById('routes-data').dataset.saveRoute;

            // Send AJAX request to save the route
            fetch(saveRouteUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ routeId: routeId })
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Show success message
                        successMessage.classList.add('show');

                        // Hide success message after animation
                        setTimeout(() => {
                            successMessage.classList.remove('show');
                        }, 3000);

                        // Update button state
                        button.innerHTML = '<i class="fas fa-check me-1"></i>Guardada';
                        button.disabled = true;
                        button.classList.add('btn-success');
                        button.classList.remove('btn-primary');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                });
        });
    });
});