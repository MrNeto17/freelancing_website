document.addEventListener('DOMContentLoaded', function() {
    const contacts = document.querySelectorAll('.contact-side');
    
    contacts.forEach(contact => {
        contact.addEventListener('click', function() {
            contacts.forEach(c => c.classList.remove('active'));
            this.classList.add('active');
            localStorage.setItem('selectedContact', this.dataset.userId);
        });
    });

    const storedContact = localStorage.getItem('selectedContact');
    if(storedContact) {
        const activeContact = document.querySelector(`[data-user-id="${storedContact}"]`);
        if(activeContact) activeContact.classList.add('active');
    }
});



function togglePaymentFields() {
    const method = document.getElementById('paymentMethod').value;

    document.getElementById('creditCardFields').style.display = (method === 'credit_card') ? 'block' : 'none';
    document.getElementById('paypalFields').style.display = (method === 'paypal') ? 'block' : 'none';
    document.getElementById('bankTransferFields').style.display = (method === 'bank_transfer') ? 'block' : 'none';
}


document.addEventListener('DOMContentLoaded', togglePaymentFields);



document.addEventListener('DOMContentLoaded', function () {
    const basePrice = parseFloat(document.getElementById('paymentForm').dataset.price);
    const currencyRates = {
        USD: 0.9,
        EUR: 1,
        GBP: 1.25,
        JPY: 142.857,
    };

    function togglePaymentFields() {
        const method = document.getElementById('paymentMethod').value;

        const fields = {
            credit_card: document.getElementById('creditCardFields'),
            paypal: document.getElementById('paypalFields'),
            bank_transfer: document.getElementById('bankTransferFields'),
        };

        for (const key in fields) {
            fields[key].classList.remove('active');
        }

        if (fields[method]) {
            setTimeout(() => {
                fields[method].classList.add('active');
            }, 10);
        }
    }

    function updateConvertedAmount() {
        const currency = document.getElementById('currency').value;
        const rate = currencyRates[currency] || 1;
        const converted = (basePrice * rate).toFixed(2);

        const convertedField = document.getElementById('convertedAmount');
        convertedField.value = `${converted} ${currency}`;
    }

    document.getElementById('paymentMethod').addEventListener('change', togglePaymentFields);
    document.getElementById('currency').addEventListener('change', updateConvertedAmount);
});





//////////////////// Ajax /////////////////////

function markAsCompleted(serviceId) {
    var xhr = new XMLHttpRequest();
    xhr.open("POST", "../API/service_api.php/mark-completed", true); 
    xhr.setRequestHeader("Content-Type", "application/json"); 
    var data = JSON.stringify({
        service_id: serviceId
    });

    xhr.send(data);

    xhr.onload = function() {
        if (xhr.status === 200) {
            var response = JSON.parse(xhr.responseText);

            if (response.message) {
                alert(response.message);

                document.querySelector(".mark-completed").disabled = true;
                document.querySelector(".mark-completed").innerText = "Completed";
                button.style.backgroundColor = "gray";
            } else if (response.error) {
               
                alert(response.error);
            }
        } else {
            alert("An error occurred while marking the service as completed.");
        }
    };
}


function deleteService(serviceId) {
    const confirmed = confirm("Are you sure you want to delete this service? This action cannot be undone.");
    if (!confirmed) return;
    
    var xhr = new XMLHttpRequest();
    xhr.open("POST", "../API/service_api.php/delete-service", true);
    xhr.setRequestHeader("Content-Type", "application/json");

    var data = JSON.stringify({
        service_id: serviceId
    });

    xhr.send(data);

    xhr.onload = function () {
        if (xhr.status === 200) {
            var response = JSON.parse(xhr.responseText);

            if (response.message) {
                alert(response.message);
                window.location.href = "../pages/index.php";
            } else if (response.error) {
                alert(response.error);
            }
        } else {
            alert("An error occurred while deleting the service.");
        }
    };
}

function addCategory() {
    const categoryName = document.getElementById("category_name").value;

    if (categoryName.trim() === "") {
        alert("Category name cannot be empty.");
        return;
    }

    var xhr = new XMLHttpRequest();
    xhr.open("POST", "../API/category_api.php/add-category", true); 
    xhr.setRequestHeader("Content-Type", "application/json");

    var data = JSON.stringify({
        name: categoryName
    });

    xhr.send(data);

    xhr.onload = function() {
        if (xhr.status === 200) {
            var response = JSON.parse(xhr.responseText);

            if (response.message) {
                alert(response.message);
                location.reload();  
            } else if (response.error) {
                alert(response.error);
            }
        } else {
            alert("An error occurred while adding the category.");
        }
    };
}

function deleteCategory(categoryName) {
    if (confirm(`Are you sure you want to delete the category: ${categoryName}?`)) {
        var xhr = new XMLHttpRequest();
        xhr.open("POST", "../API/category_api.php/delete-category", true); 
        xhr.setRequestHeader("Content-Type", "application/json");

        var data = JSON.stringify({
            name: categoryName
        });

        xhr.send(data);

        xhr.onload = function() {
            if (xhr.status === 200) {
                var response = JSON.parse(xhr.responseText);

                if (response.message) {
                    alert(response.message);
                    location.reload();  
                } else if (response.error) {
                    alert(response.error);
                }
            } else {
                alert("An error occurred while deleting the category.");
            }
        };
    }
}


function deleteImage(url){
    confirm("Are you sure you want to delete this image? This action cannot be undone.");
    if(!confirm) return;
    var xhr = new XMLHttpRequest();
    xhr.open("POST", "../API/serviceImage_api.php/delete-image", true);
    xhr.setRequestHeader("Content-Type", "application/json");

    var data = JSON.stringify({
        url: url
    });

    xhr.send(data);
    xhr.onload = function() {
        if (xhr.status === 200) {
            var response = JSON.parse(xhr.responseText);

            if (response.message) {
                alert(response.message);
                location.reload();  
            } else if (response.error) {
                alert(response.error);
            }
        } else {
            alert("An error occurred while deleting the Image.");
        }
    };
}

function ElevateTheUser(userId) {
    if(!confirm("Are you sure you want to elevate this user?")) return;
    var xhr = new XMLHttpRequest();
    xhr.open("POST", "../API/user_api.php/elevate-user", true); 
    xhr.setRequestHeader("Content-Type", "application/json"); 

    var data = JSON.stringify({
        user_id: userId
    });

    xhr.send(data);

    xhr.onload = function() {
        if (xhr.status === 200) {
            var response = JSON.parse(xhr.responseText);

            if (response.message) {
                alert(response.message);
                location.reload();  
            } else if (response.error) {
                alert(response.error);
            }
        } else {
            alert("An error occurred while elevating the user.");
        }
    };
}

function DeleteMessage(messageId) {
    if(!confirm("Are you sure you want to delete this message?")) return;
    var xhr = new XMLHttpRequest();
    xhr.open("DELETE", "../API/message_api.php/delete-message", true); 
    xhr.setRequestHeader("Content-Type", "application/json"); 

    var data = JSON.stringify({
        message_id: messageId
    });

    xhr.send(data);

    xhr.onload = function() {
        if (xhr.status === 200) {
            var response = JSON.parse(xhr.responseText);

            if (response.message) {
                alert(response.message);
                location.reload();  
            } else if (response.error) {
                alert(response.error);
            }
        } else {
            alert("An error occurred while deleting the message.");
        }
    };
}


document.getElementById('search-input').addEventListener('input', function () {
    const query = this.value.toLowerCase();

    fetch('/API/service_api.php/search-services?query=' + encodeURIComponent(query))
        .then(res => res.json())  // Parse the response as JSON
        .then(data => {
            console.log('API data:', data);
            
            const services = data.services || [];  
            const filtered = services.filter(service =>
                service.title.toLowerCase().includes(query)  
            );

            const resultsDiv = document.getElementById('results');
            resultsDiv.innerHTML = ''; 

            filtered.forEach(service => {
                const card = document.createElement('section');
                card.className = 'service-card';

                card.innerHTML = `
                    <header class="service-header">
                        <h2>${escapeHtml(service.title)}</h2>
                    </header>
                    <article class="service-details">
                        <p><strong>Freelancer:</strong> ${escapeHtml(service.freelancer.name)}</p>
                        <p>${escapeHtml(service.category)}</p>
                    </article>
                    <footer class="service-footer">
                        <p>Time to deliver: ${service.delivery_time} days</p>
                        <p>Price: $${service.price}</p>
                    </footer>
                    <div class="service-actions">
                        <a href="/pages/service_page.php?service_id=${service.id}" class="order">See more</a>
                    </div>
                `;
                
                resultsDiv.appendChild(card);
            });
        })
        .catch(err => {
            console.error('Error fetching data:', err);
        });
});

function escapeHtml(str) {
    return str
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#039;');
}
