document.addEventListener("DOMContentLoaded", () => {
  const steps = document.querySelectorAll(".step");
  const progress = document.querySelector(".progress");
  const form = document.getElementById("resumeForm");
  let current = 0;

  const showStep = (i) => {
    steps.forEach((s, idx) => s.classList.toggle("active", idx === i));
    progress.style.width = ((i + 1) / steps.length) * 100 + "%";
  };

  document.getElementById("nextBtn").onclick = () => {
    if (current < steps.length - 1) current++;
    showStep(current);
  };
  document.getElementById("prevBtn").onclick = () => {
    if (current > 0) current--;
    showStep(current);
  };

  /* === Dynamic Repeatable Sections === */
  const sections = {
    education: document.getElementById("educationContainer"),
    experience: document.getElementById("experienceContainer"),
    projects: document.getElementById("projectsContainer"),
    skills: document.getElementById("skillsContainer"),
  };

  const createFieldGroup = (type) => {
    const div = document.createElement("div");
    div.className = "field-group";

    if (type === "education") {
      div.innerHTML = `
        <input type="text" placeholder="Institution" class="edu-institution" required>
        <input type="text" placeholder="Passing Year" class="edu-year">
        <input type="text" placeholder="Subject/Group" class="edu-subject">
        <input type="text" placeholder="Result" class="edu-result">
        <button type="button" class="remove-btn">✖</button>
      `;
    } else if (type === "experience") {
      div.innerHTML = `
        <input type="text" placeholder="Job Title" class="exp-title">
        <input type="text" placeholder="Company" class="exp-company">
        <input type="text" placeholder="Years (e.g., 2021–2024)" class="exp-years">
        <textarea placeholder="Job Description" class="exp-desc"></textarea>
        <button type="button" class="remove-btn">✖</button>
      `;
    } else if (type === "projects") {
      div.innerHTML = `
        <input type="text" placeholder="Project Title" class="proj-title">
        <textarea placeholder="Description" class="proj-desc"></textarea>
        <button type="button" class="remove-btn">✖</button>
      `;
    } else if (type === "skills") {
      div.innerHTML = `
        <input type="text" placeholder="Skill Name" class="skill-item">
        <button type="button" class="remove-btn">✖</button>
      `;
    }

    div.querySelector(".remove-btn").onclick = () => div.remove();
    return div;
  };

  document.querySelectorAll(".add-btn").forEach((btn) => {
    btn.onclick = () => {
      const type = btn.dataset.section;
      sections[type].appendChild(createFieldGroup(type));
    };
  });

  document.getElementById("addSkill").onclick = () => {
    sections.skills.appendChild(createFieldGroup("skills"));
  };

  /* === On Submit === */
  form.onsubmit = async (e) => {
    e.preventDefault();

    // Gather all section data
    const education = Array.from(document.querySelectorAll("#educationContainer .field-group")).map(e => ({
      institution: e.querySelector(".edu-institution").value,
      year: e.querySelector(".edu-year").value,
      subject: e.querySelector(".edu-subject").value,
      result: e.querySelector(".edu-result").value
    }));

    const experience = Array.from(document.querySelectorAll("#experienceContainer .field-group")).map(e => ({
      title: e.querySelector(".exp-title").value,
      company: e.querySelector(".exp-company").value,
      years: e.querySelector(".exp-years").value,
      desc: e.querySelector(".exp-desc").value
    }));

    const projects = Array.from(document.querySelectorAll("#projectsContainer .field-group")).map(e => ({
      title: e.querySelector(".proj-title").value,
      desc: e.querySelector(".proj-desc").value
    }));

    const skills = Array.from(document.querySelectorAll("#skillsContainer .skill-item"))
      .map(i => i.value)
      .filter(Boolean);

    // Prepare data
    const fd = new FormData(form);
    fd.append("education", JSON.stringify(education));
    fd.append("experience", JSON.stringify(experience));
    fd.append("projects", JSON.stringify(projects));
    fd.append("skills", JSON.stringify(skills));

    const res = await fetch("save_resume.php", { method: "POST", body: fd });
    const text = await res.text();
    alert(text);
    window.location = "generate_pdf.php";
  };

  showStep(current);
});

