/**
 * JavaScript principal del Dashboard Clínico
 * Gestor de Citas Médicas
 */

function initDashboard() {
    "use strict";

    // Elementos UI
    const sidebar = document.getElementById("sidebar");
    const openSidebarBtn = document.getElementById("openSidebar");
    const closeSidebarBtn = document.getElementById("closeSidebar");
    const navLinks = document.querySelectorAll(".nav-link");
    const sections = document.querySelectorAll(".section");

    // Vista Calendario
    const calendarGrid = document.getElementById("calendarGrid");
    const viewModeSelect = document.getElementById("viewMode");
    const prevBtn = document.getElementById("prevBtn");
    const nextBtn = document.getElementById("nextBtn");
    const currentLabel = document.getElementById("currentLabel");

    // Formulario citas
    const formAddAppointment = document.getElementById("formAddAppointment");
    const appointmentsList = document.getElementById("appointmentsList");

    // Reportes
    const exportExcelBtn = document.getElementById("exportExcel");
    const exportPdfBtn = document.getElementById("exportPdf");
    const filterDateFrom = document.getElementById("filterDateFrom");
    const filterDateTo = document.getElementById("filterDateTo");
    const applyFilterBtn = document.getElementById("applyFilter");
    const clearFilterBtn = document.getElementById("clearFilter");

    // Configurar servicio
    const formBlockDay = document.getElementById("formBlockDay");
    const blockedDaysList = document.getElementById("blockedDaysList");
    const blockDateInput = document.getElementById("blockDate");
    const openHourInput = document.getElementById("openHour");
    const closeHourInput = document.getElementById("closeHour");

    // Variables estado
    let currentDate = new Date();
    let currentViewMode = "month"; // "month" or "week"
    let appointments = [];
    let blockedDays = [];
    let filteredAppointments = null; // Para reportes con filtro

    // ---------------------- Funciones de comunicación con API ----------------------
    async function loadAppointments() {
        try {
            const response = await fetch('../api/citas_api.php?action=getAll');
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            const data = await response.json();
            if (data.success) {
                appointments = data.appointments;
                renderAppointments();
                renderCalendar();
            } else {
                console.error('Error en respuesta API:', data.message);
                showNotification('Error cargando citas: ' + data.message, 'error');
            }
        } catch (error) {
            console.error('Error cargando citas:', error);
            showNotification('Error de conexión al cargar citas', 'error');
        }
    }

    async function loadBlockedDays() {
        try {
            const response = await fetch('../api/citas_api.php?action=getBlockedDays');
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            const data = await response.json();
            if (data.success) {
                blockedDays = data.blockedDays;
                renderBlockedDays();
                renderCalendar();
            } else {
                console.error('Error en respuesta API:', data.message);
                showNotification('Error cargando días bloqueados: ' + data.message, 'error');
            }
        } catch (error) {
            console.error('Error cargando días bloqueados:', error);
            showNotification('Error de conexión al cargar días bloqueados', 'error');
        }
    }

    async function addAppointment(appt) {
        try {
            const response = await fetch('../api/citas_api.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    action: 'add',
                    appointment: appt
                })
            });
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            const data = await response.json();
            if (data.success) {
                await loadAppointments();
                showNotification('Cita agregada exitosamente', 'success');
            } else {
                showNotification('Error: ' + data.message, 'error');
            }
        } catch (error) {
            console.error('Error agregando cita:', error);
            showNotification('Error de conexión: ' + error.message, 'error');
        }
    }

    async function updateAppointment(id, updated) {
        try {
            const response = await fetch('../api/citas_api.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    action: 'update',
                    id: id,
                    appointment: updated
                })
            });
            const data = await response.json();
            if (data.success) {
                await loadAppointments();
                showNotification('Cita actualizada exitosamente');
            } else {
                showNotification('Error: ' + data.message, 'error');
            }
        } catch (error) {
            console.error('Error actualizando cita:', error);
            showNotification('Error de conexión', 'error');
        }
    }

    async function deleteAppointment(id) {
        try {
            const response = await fetch('../api/citas_api.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    action: 'delete',
                    id: id
                })
            });
            const data = await response.json();
            if (data.success) {
                await loadAppointments();
                showNotification('Cita eliminada exitosamente');
            } else {
                showNotification('Error: ' + data.message, 'error');
            }
        } catch (error) {
            console.error('Error eliminando cita:', error);
            showNotification('Error de conexión', 'error');
        }
    }

    async function addBlockedDay(block) {
        try {
            const response = await fetch('../api/citas_api.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    action: 'addBlockedDay',
                    blockedDay: block
                })
            });
            const data = await response.json();
            if (data.success) {
                await loadBlockedDays();
                showNotification('Día bloqueado exitosamente');
            } else {
                showNotification('Error: ' + data.message, 'error');
            }
        } catch (error) {
            console.error('Error bloqueando día:', error);
            showNotification('Error de conexión', 'error');
        }
    }

    async function deleteBlockedDay(dateStr) {
        try {
            const response = await fetch('../api/citas_api.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    action: 'deleteBlockedDay',
                    date: dateStr
                })
            });
            const data = await response.json();
            if (data.success) {
                await loadBlockedDays();
                showNotification('Bloqueo eliminado exitosamente');
            } else {
                showNotification('Error: ' + data.message, 'error');
            }
        } catch (error) {
            console.error('Error eliminando bloqueo:', error);
            showNotification('Error de conexión', 'error');
        }
    }

    // ---------------------- Mostrar secciones ----------------------
    function showSection(name) {
        sections.forEach(sec => {
            sec.classList.toggle("hidden", sec.id !== name);
        });
        navLinks.forEach(btn => {
            btn.classList.toggle("bg-indigo-100", btn.dataset.section === name);
        });
    }

    // ---------------------- Sidebar toggles ----------------------
    openSidebarBtn.addEventListener("click", () => {
        sidebar.classList.remove("-translate-x-full");
    });
    closeSidebarBtn.addEventListener("click", () => {
        sidebar.classList.add("-translate-x-full");
    });

    navLinks.forEach(link => {
        link.addEventListener("click", () => {
            showSection(link.dataset.section);
            if(window.innerWidth < 1024) sidebar.classList.add("-translate-x-full");
        });
    });

    // Inicializamos vista
    showSection("overview");

    // ---------------------- Funciones para fechas ----------------------
    function formatDateTime(dtStr) {
        if (!dtStr) return 'N/A';
        const dt = new Date(dtStr);
        if (isNaN(dt.getTime())) return 'Fecha inválida';
        return dt.toLocaleString("es-MX", { dateStyle: "short", timeStyle: "short" });
    }
    function formatDate(dt) {
        if (!dt) return 'N/A';
        if (typeof dt === 'string') dt = new Date(dt);
        if (isNaN(dt.getTime())) return 'Fecha inválida';
        return dt.toLocaleDateString("es-MX");
    }

    // ---------------------- Render listado de citas en tabla ----------------------
    function renderAppointments(list = appointments) {
        appointmentsList.innerHTML = "";
        if (list.length === 0) {
            appointmentsList.innerHTML = `<tr><td colspan="7" class="text-center py-4 text-gray-500">No hay citas</td></tr>`;
            return;
        }
        list.forEach(appt => {
            const tr = document.createElement("tr");
            tr.classList.add("border-b");
            
            // Determinar color del estado
            let statusClass = '';
            let statusText = '';
            switch(appt.estado) {
                case 'pendiente':
                    statusClass = 'bg-yellow-100 text-yellow-800';
                    statusText = 'Pendiente';
                    break;
                case 'aprobada':
                    statusClass = 'bg-green-100 text-green-800';
                    statusText = 'Aprobada';
                    break;
                case 'rechazada':
                    statusClass = 'bg-red-100 text-red-800';
                    statusText = 'Rechazada';
                    break;
                default:
                    statusClass = 'bg-gray-100 text-gray-800';
                    statusText = appt.estado || 'Pendiente';
            }

            tr.innerHTML = `
                <td class="border px-2 py-1">${appt.patientName || appt.nombre_paciente}</td>
                <td class="border px-2 py-1">${appt.patientPhone || appt.telefono_paciente}</td>
                <td class="border px-2 py-1">${formatDateTime(appt.fecha)}</td>
                <td class="border px-2 py-1 text-center">${appt.patientWeight || appt.peso_paciente || ''}</td>
                <td class="border px-2 py-1">${appt.notas || ""}</td>
                <td class="border px-2 py-1 text-center">
                    <span class="px-2 py-1 rounded-full text-xs font-medium ${statusClass}">${statusText}</span>
                </td>
                <td class="border px-2 py-1 text-center space-x-1">
                    <button class="edit-btn px-2 py-1 bg-indigo-600 text-white rounded text-sm" data-id="${appt.id}" title="Editar"><i class="ph-pencil"></i></button>
                    <button class="del-btn px-2 py-1 bg-red-600 text-white rounded text-sm" data-id="${appt.id}" title="Eliminar"><i class="ph-trash"></i></button>
                    ${appt.estado === 'pendiente' ? `
                        <button class="approve-btn px-2 py-1 bg-green-600 text-white rounded text-sm" data-id="${appt.id}" title="Aprobar"><i class="ph-check"></i></button>
                        <button class="reject-btn px-2 py-1 bg-red-600 text-white rounded text-sm" data-id="${appt.id}" title="Rechazar"><i class="ph-x"></i></button>
                    ` : ''}
                </td>
            `;
            appointmentsList.appendChild(tr);
        });

        // Botones editar
        document.querySelectorAll(".edit-btn").forEach(btn => {
            btn.onclick = e => {
                const id = btn.dataset.id;
                const appt = appointments.find(a => a.id === id);
                if (!appt) return showNotification("Cita no encontrada", 'error');
                fillForm(appt);
            };
        });

        // Botones eliminar
        document.querySelectorAll(".del-btn").forEach(btn => {
            btn.onclick = e => {
                const id = btn.dataset.id;
                if (confirm("¿Eliminar esta cita?")) {
                    deleteAppointment(id);
                }
            };
        });

        // Botones aprobar
        document.querySelectorAll(".approve-btn").forEach(btn => {
            btn.onclick = e => {
                const id = btn.dataset.id;
                if (confirm("¿Aprobar esta cita?")) {
                    updateAppointment(id, { estado: 'aprobada' });
                }
            };
        });

        // Botones rechazar
        document.querySelectorAll(".reject-btn").forEach(btn => {
            btn.onclick = e => {
                const id = btn.dataset.id;
                if (confirm("¿Rechazar esta cita?")) {
                    updateAppointment(id, { estado: 'rechazada' });
                }
            };
        });
    }

    // Llenar formulario para editar
    function fillForm(appt) {
        formAddAppointment.patientName.value = appt.patientName || appt.nombre_paciente || '';
        formAddAppointment.patientPhone.value = appt.patientPhone || appt.telefono_paciente || '';
        formAddAppointment.patientWeight.value = appt.patientWeight || appt.peso_paciente || '';
        formAddAppointment.patientEmail.value = appt.patientEmail || appt.correo_paciente || '';
        formAddAppointment.appointmentDate.value = appt.fecha.substring(0,16);
        formAddAppointment.notes.value = appt.notas || "";
        formAddAppointment.dataset.editId = appt.id;
        formAddAppointment.querySelector("button[type=submit]").textContent = "Guardar cambios";
        // Scroll al formulario
        formAddAppointment.scrollIntoView({behavior:"smooth"});
    }

    // Limpiar formulario
    function clearForm() {
        formAddAppointment.reset();
        delete formAddAppointment.dataset.editId;
        formAddAppointment.querySelector("button[type=submit]").textContent = "Agregar cita";
    }

    // ---------------------- Calendario ----------------------
    // Variables para calendario
    let calendarStartDate = new Date(currentDate.getFullYear(), currentDate.getMonth(), 1);

    // Días de la semana
    const daysOfWeek = ["Dom", "Lun", "Mar", "Mié", "Jue", "Vie", "Sáb"];

    // Render calendario según modo
    function renderCalendar() {
        calendarGrid.innerHTML = "";
        if (currentViewMode === "month") {
            renderMonthView();
        } else {
            renderWeekView();
        }
    }

    // Mes: render calendario mensual
    function renderMonthView() {
        const year = calendarStartDate.getFullYear();
        const month = calendarStartDate.getMonth();
        currentLabel.textContent = calendarStartDate.toLocaleString("es-MX", { month: "long", year: "numeric" });

        // Primer día mes
        const firstDay = new Date(year, month, 1);
        // Día de la semana que inicia (0=Domingo,...)
        const startWeekDay = firstDay.getDay();

        // Total días mes
        const daysInMonth = new Date(year, month + 1, 0).getDate();

        // Mostrar nombres días
        daysOfWeek.forEach(day => {
            const d = document.createElement("div");
            d.textContent = day;
            d.className = "font-semibold text-center";
            calendarGrid.appendChild(d);
        });

        // Celdas vacías antes del primer día
        for (let i = 0; i < startWeekDay; i++) {
            const empty = document.createElement("div");
            calendarGrid.appendChild(empty);
        }

        // Días del mes
        for (let day = 1; day <= daysInMonth; day++) {
            const date = new Date(year, month, day);
            const dateStr = date.toISOString().slice(0,10);

            const cell = document.createElement("div");
            cell.className = "border border-gray-300 rounded-lg p-2 min-h-[100px] flex flex-col justify-start";
            if (isBlockedDay(dateStr)) cell.classList.add("bg-red-100");
            if (isToday(date)) cell.classList.add("bg-indigo-100");

            // Fecha
            const dayLabel = document.createElement("div");
            dayLabel.textContent = day;
            dayLabel.className = "font-semibold mb-1";
            cell.appendChild(dayLabel);

            // Citas para ese día
            const dayAppointments = appointments.filter(a => a.fecha.slice(0,10) === dateStr);
            if(dayAppointments.length === 0) {
                const emptyMsg = document.createElement("small");
                emptyMsg.textContent = isBlockedDay(dateStr) ? "Sin servicio" : "Sin citas";
                emptyMsg.className = "text-gray-400";
                cell.appendChild(emptyMsg);
            } else {
                dayAppointments.forEach(a => {
                    const apptDiv = document.createElement("div");
                    apptDiv.className = "text-xs bg-indigo-200 rounded px-1 mb-0.5 cursor-pointer hover:bg-indigo-300 truncate";
                    apptDiv.title = `${a.patientName || a.nombre_paciente} - ${formatDateTime(a.fecha)}\nNotas: ${a.notas || ""}`;
                    apptDiv.textContent = `${new Date(a.fecha).toLocaleTimeString("es-MX",{hour:"2-digit",minute:"2-digit"})} - ${a.patientName || a.nombre_paciente}`;
                    apptDiv.onclick = () => showAppointmentDetails(a);
                    cell.appendChild(apptDiv);
                });
            }
            calendarGrid.appendChild(cell);
        }
    }

    // Semana: render calendario semanal
    function renderWeekView() {
        const startOfWeek = getStartOfWeek(calendarStartDate);
        const endOfWeek = new Date(startOfWeek);
        endOfWeek.setDate(startOfWeek.getDate() + 6);
        currentLabel.textContent = `${formatDate(startOfWeek)} - ${formatDate(endOfWeek)}`;

        calendarGrid.innerHTML = "";
        calendarGrid.style.gridTemplateColumns = "repeat(7, 1fr)";

        // Nombres días
        daysOfWeek.forEach((day, i) => {
            const header = document.createElement("div");
            header.className = "font-semibold text-center border-b pb-1";
            const date = new Date(startOfWeek);
            date.setDate(date.getDate() + i);
            header.textContent = `${day} ${date.getDate()}/${date.getMonth()+1}`;
            calendarGrid.appendChild(header);
        });

        // Mostrar citas día por día
        for(let i=0; i<7; i++) {
            const date = new Date(startOfWeek);
            date.setDate(date.getDate() + i);
            const dateStr = date.toISOString().slice(0,10);

            const cell = document.createElement("div");
            cell.className = "border border-gray-300 rounded-lg p-2 min-h-[120px] flex flex-col";
            if (isBlockedDay(dateStr)) cell.classList.add("bg-red-100");
            if (isToday(date)) cell.classList.add("bg-indigo-100");

            // Citas del día
            const dayAppointments = appointments.filter(a => a.fecha.slice(0,10) === dateStr);
            if(dayAppointments.length === 0) {
                const emptyMsg = document.createElement("small");
                emptyMsg.textContent = isBlockedDay(dateStr) ? "Sin servicio" : "Sin citas";
                emptyMsg.className = "text-gray-400";
                cell.appendChild(emptyMsg);
            } else {
                dayAppointments.forEach(a => {
                    const apptDiv = document.createElement("div");
                    apptDiv.className = "text-xs bg-indigo-200 rounded px-1 mb-0.5 cursor-pointer hover:bg-indigo-300 truncate";
                    apptDiv.title = `${a.patientName || a.nombre_paciente} - ${formatDateTime(a.fecha)}\nNotas: ${a.notas || ""}`;
                    apptDiv.textContent = `${new Date(a.fecha).toLocaleTimeString("es-MX",{hour:"2-digit",minute:"2-digit"})} - ${a.patientName || a.nombre_paciente}`;
                    apptDiv.onclick = () => showAppointmentDetails(a);
                    cell.appendChild(apptDiv);
                });
            }
            calendarGrid.appendChild(cell);
        }
    }

    // Fecha hoy?
    function isToday(date) {
        const now = new Date();
        return date.getDate() === now.getDate() && date.getMonth() === now.getMonth() && date.getFullYear() === now.getFullYear();
    }

    // Inicio semana domingo
    function getStartOfWeek(date) {
        const d = new Date(date);
        const day = d.getDay();
        d.setDate(d.getDate() - day);
        return d;
    }

    // Avanzar o retroceder calendario
    prevBtn.onclick = () => {
        if(currentViewMode === "month") {
            calendarStartDate.setMonth(calendarStartDate.getMonth() - 1);
        } else {
            calendarStartDate.setDate(calendarStartDate.getDate() - 7);
        }
        renderCalendar();
    };
    nextBtn.onclick = () => {
        if(currentViewMode === "month") {
            calendarStartDate.setMonth(calendarStartDate.getMonth() + 1);
        } else {
            calendarStartDate.setDate(calendarStartDate.getDate() + 7);
        }
        renderCalendar();
    };

    viewModeSelect.onchange = () => {
        currentViewMode = viewModeSelect.value;
        calendarStartDate = new Date(currentDate.getFullYear(), currentDate.getMonth(), currentDate.getDate());
        renderCalendar();
    };

    // ---------------------- Validaciones citas ----------------------
    function canSchedule(dateTimeStr) {
        const dateTime = new Date(dateTimeStr);
        const dateStr = dateTime.toISOString().slice(0,10);

        // No agendar en días bloqueados
        const block = blockedDays.find(b => b.fecha === dateStr);
        if (block) return false;

        // Verificar horario apertura/cierre
        if (block) {
            const [openH, openM] = block.hora_apertura.split(":").map(Number);
            const [closeH, closeM] = block.hora_cierre.split(":").map(Number);
            const openDate = new Date(dateTime);
            openDate.setHours(openH, openM, 0, 0);
            const closeDate = new Date(dateTime);
            closeDate.setHours(closeH, closeM, 0, 0);
            if (dateTime < openDate || dateTime > closeDate) return false;
        }

        // Si no está bloqueado, validar horario estándar
        if (!block) {
            const defaultOpen = "08:00";
            const defaultClose = "17:00";
            const [openH, openM] = defaultOpen.split(":").map(Number);
            const [closeH, closeM] = defaultClose.split(":").map(Number);
            const openDate = new Date(dateTime);
            openDate.setHours(openH, openM, 0, 0);
            const closeDate = new Date(dateTime);
            closeDate.setHours(closeH, closeM, 0, 0);
            if (dateTime < openDate || dateTime > closeDate) return false;
        }

        return true;
    }

    // ---------------------- Formulario nueva cita ----------------------
    formAddAppointment.addEventListener("submit", e => {
        e.preventDefault();

        const patientName = formAddAppointment.patientName.value.trim();
        const patientPhone = formAddAppointment.patientPhone.value.trim();
        const patientWeight = Number(formAddAppointment.patientWeight.value);
        const patientEmail = formAddAppointment.patientEmail.value.trim();
        const date = formAddAppointment.appointmentDate.value;
        const notes = formAddAppointment.notes.value.trim();

        if(!canSchedule(date)) {
            showNotification("No se puede agendar en ese horario o día bloqueado.", 'error');
            return;
        }

        if (formAddAppointment.dataset.editId) {
            updateAppointment(formAddAppointment.dataset.editId, {
                patientName, patientPhone, patientWeight, patientEmail, date, notes
            });
            clearForm();
        } else {
            addAppointment({
                patientName, patientPhone, patientWeight, patientEmail, date, notes
            });
            clearForm();
        }
    });

    // ---------------------- Exportar Excel ----------------------
    exportExcelBtn.onclick = () => {
        const data = (filteredAppointments || appointments).map(a => ({
            ID: a.id,
            Paciente: a.patientName || a.nombre_paciente,
            Teléfono: a.patientPhone || a.telefono_paciente,
            Peso: a.patientWeight || a.peso_paciente,
            Correo: a.patientEmail || a.correo_paciente,
            Fecha: formatDateTime(a.fecha),
            Notas: a.notas,
            Estado: a.estado
        }));

        if(data.length === 0){
            showNotification("No hay citas para exportar.", 'warning');
            return;
        }

        const worksheet = XLSX.utils.json_to_sheet(data);
        const workbook = XLSX.utils.book_new();
        XLSX.utils.book_append_sheet(workbook, worksheet, "Citas");

        XLSX.writeFile(workbook, "Citas.xlsx");
    };

    // ---------------------- Exportar PDF ----------------------
    exportPdfBtn.onclick = () => {
        const { jsPDF } = window.jspdf;
        const doc = new jsPDF();

        const appts = filteredAppointments || appointments;
        if(appts.length === 0){
            showNotification("No hay citas para exportar.", 'warning');
            return;
        }

        doc.setFontSize(18);
        doc.text("Reporte de Citas", 14, 20);
        doc.setFontSize(11);
        doc.setTextColor(100);

        const columns = ["Paciente", "Teléfono", "Peso", "Correo", "Fecha y Hora", "Notas", "Estado"];
        const rows = appts.map(a => [
            a.patientName || a.nombre_paciente,
            a.patientPhone || a.telefono_paciente,
            (a.patientWeight || a.peso_paciente || '').toString(),
            a.patientEmail || a.correo_paciente,
            formatDateTime(a.fecha),
            a.notas || "",
            a.estado
        ]);

        // AutoTable
        if(doc.autoTable === undefined) {
            showNotification("No se cargó la librería autoTable de jsPDF, no se puede exportar.", 'error');
            return;
        }

        doc.autoTable({
            head: [columns],
            body: rows,
            startY: 30,
            styles: { fontSize: 8, cellPadding: 2 },
            headStyles: { fillColor: [49, 130, 206] },
            theme: "grid"
        });

        doc.save("Citas.pdf");
    };

    // ---------------------- Filtros para reportes ----------------------
    applyFilterBtn.onclick = () => {
        const from = filterDateFrom.value;
        const to = filterDateTo.value;
        if(!from || !to) return showNotification("Selecciona ambas fechas para filtrar.", 'warning');

        const fromDate = new Date(from);
        const toDate = new Date(to);
        if(fromDate > toDate) return showNotification("Fecha 'desde' debe ser menor o igual a fecha 'hasta'.", 'error');

        filteredAppointments = appointments.filter(a => {
            const apptDate = new Date(a.fecha.slice(0,10));
            return apptDate >= fromDate && apptDate <= toDate;
        });
        showNotification(`Filtro aplicado: ${filteredAppointments.length} citas encontradas.`);
    };
    clearFilterBtn.onclick = () => {
        filteredAppointments = null;
        filterDateFrom.value = "";
        filterDateTo.value = "";
        showNotification("Filtro eliminado.");
    };

    // ---------------------- Bloquear días sin servicio ----------------------
    function isBlockedDay(dateStr) {
        return blockedDays.some(b => b.fecha === dateStr);
    }

    formBlockDay.addEventListener("submit", e => {
        e.preventDefault();
        const date = blockDateInput.value;
        const open = openHourInput.value;
        const close = closeHourInput.value;

        if(open >= close) {
            showNotification("La hora de apertura debe ser menor a la hora de cierre.", 'error');
            return;
        }
        
        addBlockedDay({ fecha: date, hora_apertura: open, hora_cierre: close });
        formBlockDay.reset();
    });

    function renderBlockedDays() {
        blockedDaysList.innerHTML = "";
        if(blockedDays.length === 0) {
            blockedDaysList.innerHTML = `<tr><td colspan="4" class="text-center py-4 text-gray-500">No hay días bloqueados</td></tr>`;
            return;
        }
        blockedDays.forEach(block => {
            const tr = document.createElement("tr");
            tr.className = "border-b";
            tr.innerHTML = `
                <td class="border px-3 py-1">${block.fecha}</td>
                <td class="border px-3 py-1 text-center">${block.hora_apertura}</td>
                <td class="border px-3 py-1 text-center">${block.hora_cierre}</td>
                <td class="border px-3 py-1 text-center">
                    <button class="del-block-btn bg-red-600 hover:bg-red-700 text-white rounded px-2 py-1 text-sm" data-date="${block.fecha}" title="Eliminar bloqueo"><i class="ph-trash"></i></button>
                </td>
            `;
            blockedDaysList.appendChild(tr);
        });
        document.querySelectorAll(".del-block-btn").forEach(btn => {
            btn.onclick = () => {
                const date = btn.dataset.date;
                if(confirm(`Eliminar bloqueo del día ${date}?`)) {
                    deleteBlockedDay(date);
                }
            };
        });
    }

    // ---------------------- Funciones auxiliares ----------------------
    function showAppointmentDetails(appt) {
        const details = `
Paciente: ${appt.patientName || appt.nombre_paciente}
Teléfono: ${appt.patientPhone || appt.telefono_paciente}
Fecha: ${formatDateTime(appt.fecha)}
Peso: ${appt.patientWeight || appt.peso_paciente || 'N/A'}
Estado: ${appt.estado}
Notas: ${appt.notas || "Ninguna"}`;
        alert(details);
    }

    function showNotification(msg, type = 'info') {
        // Crear notificación visual
        const notification = document.createElement('div');
        notification.className = `fixed top-4 right-4 p-4 rounded-lg shadow-lg z-50 max-w-sm ${
            type === 'error' ? 'bg-red-500 text-white' :
            type === 'success' ? 'bg-green-500 text-white' :
            type === 'warning' ? 'bg-yellow-500 text-white' :
            'bg-blue-500 text-white'
        }`;
        notification.textContent = msg;
        
        document.body.appendChild(notification);
        
        // Auto-remover después de 3 segundos
        setTimeout(() => {
            notification.remove();
        }, 3000);
    }

    // Evento para limpiar formulario edición
    formAddAppointment.querySelector("button[type=submit]").addEventListener("dblclick", e => {
        clearForm();
        showNotification("Formulario limpiado.");
    });

    // ---------------------- Inicialización ----------------------
    async function init() {
        await loadAppointments();
        await loadBlockedDays();
        renderCalendar();
    }

    init();
}
