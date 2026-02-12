document.addEventListener('DOMContentLoaded', function () {
    const formContainers = document.querySelectorAll('.form-container');
    const navLinks = document.querySelectorAll('nav a');
    const homeContent = document.getElementById('home-content');

    const btnShowSignin = document.getElementById('btn-show-signin');
    const btnShowSignup = document.getElementById('btn-show-signup');
    const formSignin = document.getElementById('form-signin');
    const formSignup = document.getElementById('form-signup');

    navLinks[0].classList.add('active');

    navLinks.forEach(link => {
        link.addEventListener('click', function (e) {
            e.preventDefault();
            navLinks.forEach(l => l.classList.remove('active'));
            this.classList.add('active');

            formContainers.forEach(container => {
                container.classList.remove('active');
            });

            const formId = this.getAttribute('data-form');
            if (formId === 'home') {
                homeContent.classList.add('active');
            } else {
                const targetForm = document.getElementById(`form-${formId}`);
                if (targetForm) {
                    targetForm.classList.add('active');
                }
            }
        });
    });

    if (btnShowSignin && btnShowSignup) {
        btnShowSignin.addEventListener('click', function () {
            formSignin.style.display = 'block';
            formSignup.style.display = 'none';
            btnShowSignin.classList.remove('btn-outline-primary');
            btnShowSignin.classList.add('btn-primary');
            btnShowSignup.classList.remove('btn-success');
            btnShowSignup.classList.add('btn-outline-success');
        });

        btnShowSignup.addEventListener('click', function () {
            formSignin.style.display = 'none';
            formSignup.style.display = 'block';
            btnShowSignup.classList.remove('btn-outline-success');
            btnShowSignup.classList.add('btn-success');
            btnShowSignin.classList.remove('btn-primary');
            btnShowSignin.classList.add('btn-outline-primary');
        });

        btnShowSignin.click();
    }

    document.querySelectorAll(".delete-task-btn").forEach(btn => {
        btn.addEventListener("click", function () {
            const taskId = this.dataset.id;
            if (confirm("Are you sure you want to delete this task?")) {
                const form = document.createElement("form");
                form.method = "POST";
                form.action = "index.php";

                const input = document.createElement("input");
                input.type = "hidden";
                input.name = "task_id";
                input.value = taskId;
                form.appendChild(input);

                const action = document.createElement("input");
                action.type = "hidden";
                action.name = "delete_task";
                action.value = "1";
                form.appendChild(action);

                document.body.appendChild(form);
                form.submit();
            }
        });
    });

    document.querySelectorAll(".edit-task-btn").forEach(btn => {
        btn.addEventListener("click", function () {
            const taskId = this.dataset.id;
            const title = this.dataset.title;
            const desc = this.dataset.desc;
            const dueDate = this.dataset.date;

            const formUpdate = document.getElementById("form-update");
            formUpdate.querySelector("#title_up").value = title;
            formUpdate.querySelector("#description_up").value = desc;
            formUpdate.querySelector("#due_date_up").value = dueDate;

            let hidden = formUpdate.querySelector('input[name="task_id"]');
            if (!hidden) {
                hidden = document.createElement("input");
                hidden.type = "hidden";
                hidden.name = "task_id";
                formUpdate.appendChild(hidden);
            }
            hidden.value = taskId;

            document.querySelectorAll(".form-container").forEach(f => f.classList.remove("active"));
            formUpdate.classList.add("active");
        });
    });

    const exitBtn = document.getElementById("nav-exit");
    if (exitBtn) {
        exitBtn.addEventListener("click", function () {
            if (confirm("Are you sure you want to log out?")) {
                const form = document.createElement("form");
                form.method = "POST";
                form.action = "index.php";

                const input = document.createElement("input");
                input.type = "hidden";
                input.name = "logout";
                input.value = "1";

                form.appendChild(input);
                document.body.appendChild(form);
                form.submit();
            }
        });
    }
});

function showAddForm() {
    document.querySelectorAll(".form-container").forEach(f => f.classList.remove("active"));
    const addForm = document.getElementById("form-add");
    if (addForm) {
        addForm.classList.add("active");
        addForm.reset();
    }
}