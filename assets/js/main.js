document.addEventListener('DOMContentLoaded', function () {
    const dropdownBtn = document.getElementById('dashboardDropdown');
    const dropdownMenu = document.getElementById('dashboardMenu');

    if (dropdownBtn && dropdownMenu) {
        dropdownBtn.addEventListener('click', function () {
            const open = dropdownMenu.classList.toggle('open');
            dropdownBtn.setAttribute('aria-expanded', open ? 'true' : 'false');
        });

        document.addEventListener('click', function (e) {
            if (!dropdownBtn.contains(e.target) && !dropdownMenu.contains(e.target)) {
                dropdownMenu.classList.remove('open');
                dropdownBtn.setAttribute('aria-expanded', 'false');
            }
        });
    }

    const fullNameInput = document.getElementById('full_name');
    if (fullNameInput) {
        fullNameInput.addEventListener('input', function () {
            this.value = this.value.toUpperCase();
        });
    }

    const roleSelect = document.getElementById('role');
    if (roleSelect) {
        function updateRegisterPageStyle() {
            document.body.classList.remove('register-lecturer', 'register-student');
            document.body.classList.add(roleSelect.value === 'lecturer' ? 'register-lecturer' : 'register-student');
            const card = document.querySelector('.form-card-auth');
            if (card) {
                card.classList.toggle('form-card-lecturer', roleSelect.value === 'lecturer');
            }
            const heading = document.querySelector('.form-card-auth h1');
            if (heading) {
                heading.textContent = roleSelect.value === 'lecturer' ? 'Lecturer Registration' : 'User Registration';
            }
        }
        roleSelect.addEventListener('change', updateRegisterPageStyle);
        updateRegisterPageStyle();
    }

    const questionType = document.getElementById('questionType');
    const optionsGroup = document.getElementById('optionsGroup');
    if (questionType && optionsGroup) {
        function toggleOptions() {
            optionsGroup.style.display = questionType.value === 'multiple_choice' ? 'block' : 'none';
        }
        questionType.addEventListener('change', toggleOptions);
        toggleOptions();
    }

    const welcome = document.getElementById('welcomeNotification');
    if (welcome) {
        setTimeout(function () {
            welcome.style.transition = 'box-shadow 0.5s';
            welcome.style.boxShadow = '0 16px 48px rgba(30, 64, 175, 0.35)';
        }, 500);
    }
});
