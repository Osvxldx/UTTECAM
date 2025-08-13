<?php
require_once '../config/config.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

// Verificar que sea admin
if (!isAdmin()) {
    redirect('../login.php');
}

$functions = new Functions();
$stats = $functions->getAppointmentStats();
$doctorInfo = $functions->getDoctorInfo();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Dashboard Clínico - Control de Citas</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet" />
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        /* Animaciones base */
        .fade-in { animation: fade 0.6s ease-in-out both; }
        @keyframes fade {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .slide { animation: slide 0.4s ease both; }
        @keyframes slide {
            from { max-height: 0; opacity: 0; }
            to { max-height: 800px; opacity: 1; }
        }
        ::-webkit-scrollbar { width: 8px; }
        ::-webkit-scrollbar-thumb { background: #9ca3af; border-radius: 4px; }

        /* Para mejorar visibilidad en modo claro */
        body {
            background-color: #f8fafc;
            color: #334155;
        }
        /* Botones */
        button {
            transition: background-color 0.2s ease, color 0.2s ease;
        }
        button:hover {
            background-color: #e0e7ff;
            color: #4338ca;
        }
    </style>
</head>
<body class="min-h-screen font-[Poppins]">

    <!-- Sidebar -->
    <aside id="sidebar" class="fixed inset-y-0 left-0 w-64 bg-white shadow-xl z-20 transition-transform duration-300 -translate-x-full lg:translate-x-0">
        <div class="p-6 border-b flex items-center justify-between">
            <div>
                <h2 class="text-xl font-semibold">Dashboard Clínico</h2>
                <p class="text-sm text-gray-600"><?php echo $doctorInfo['name']; ?></p>
                <p class="text-xs text-gray-500"><?php echo $doctorInfo['specialty']; ?></p>
            </div>
            <button id="closeSidebar" class="lg:hidden text-2xl"><i class="ph-x"></i></button>
        </div>
        <nav class="p-4 space-y-4">
            <button data-section="overview" class="nav-link flex items-center gap-2 w-full px-3 py-2 rounded-lg hover:bg-indigo-50"><i class="ph-gauge"></i> Resumen</button>
            <button data-section="calendar" class="nav-link flex items-center gap-2 w-full px-3 py-2 rounded-lg hover:bg-indigo-50"><i class="ph-calendar"></i> Calendario</button>
            <button data-section="reports" class="nav-link flex items-center gap-2 w-full px-3 py-2 rounded-lg hover:bg-indigo-50"><i class="ph-file-pdf"></i> Reportes</button>
            <button data-section="profile" class="nav-link flex items-center gap-2 w-full px-3 py-2 rounded-lg hover:bg-indigo-50"><i class="ph-user"></i> Gestión Citas</button>
            <button data-section="serviceSettings" class="nav-link flex items-center gap-2 w-full px-3 py-2 rounded-lg hover:bg-indigo-50"><i class="ph-sliders"></i> Configurar Servicio</button>
            <div class="border-t pt-4">
                <a href="../logout.php" class="flex items-center gap-2 w-full px-3 py-2 rounded-lg hover:bg-red-50 text-red-600">
                    <i class="ph-sign-out"></i> Cerrar Sesión
                </a>
            </div>
        </nav>
    </aside>

    <!-- Contenedor principal -->
    <div class="lg:pl-64 flex flex-col min-h-screen transition-all duration-300">
        <!-- Barra superior -->
        <header class="flex items-center justify-between bg-white shadow px-6 py-4 sticky top-0 z-10">
            <button id="openSidebar" class="lg:hidden text-2xl"><i class="ph-list"></i></button>
            <div>
                <h1 class="text-lg font-semibold">Clínica Saludable</h1>
                <p class="text-sm text-gray-600"><?php echo $doctorInfo['address']; ?></p>
            </div>
            <div id="userInfo" class="flex items-center gap-3">
                <div class="text-right text-sm">
                    <p class="font-medium" id="userName"><?php echo $_SESSION['user_name']; ?></p>
                    <p id="userRole">Merdardo G. Campos</p>
                </div>
                <div class="w-10 h-10 rounded-full overflow-hidden bg-gray-200 flex items-center justify-center">
                    <img src="../imagen/Logo.jpg" alt="Logo" class="w-full h-full object-contain">
                </div>
        </header>

        <!-- Secciones -->
        <main class="p-6 flex-1 space-y-8 overflow-y-auto">

            <!-- Resumen -->
            <section id="overview" class="section fade-in">
                <h2 class="text-xl font-semibold mb-4">Resumen</h2>
                <div class="grid md:grid-cols-3 gap-6">
                    <div class="bg-white rounded-xl shadow p-5 text-center">
                        <h3 class="font-medium mb-2">Citas hoy</h3>
                        <p id="countToday" class="text-4xl font-bold text-indigo-600"><?php echo $stats['today']; ?></p>
                    </div>
                    <div class="bg-white rounded-xl shadow p-5 text-center">
                        <h3 class="font-medium mb-2">Citas esta semana</h3>
                        <p id="countWeek" class="text-4xl font-bold text-indigo-600"><?php echo $stats['week']; ?></p>
                    </div>
                    <div class="bg-white rounded-xl shadow p-5 text-center">
                        <h3 class="font-medium mb-2">Citas este mes</h3>
                        <p id="countMonth" class="text-4xl font-bold text-indigo-600"><?php echo $stats['month']; ?></p>
                    </div>
                </div>
            </section>

            <!-- Calendario -->
            <section id="calendar" class="section fade-in hidden">
                <div class="flex items-center justify-between mb-4 flex-wrap gap-2">
                    <h2 class="text-xl font-semibold">Calendario de Citas</h2>
                    <div class="flex gap-2 items-center">
                        <select id="viewMode" class="border rounded px-2 py-1 text-sm" title="Selecciona vista">
                            <option value="month" selected>Vista Mensual</option>
                            <option value="week">Vista Semanal</option>
                        </select>
                        <button id="prevBtn" class="text-lg p-2 rounded hover:bg-indigo-100" title="Mes/Semana anterior"><i class="ph-arrow-left"></i></button>
                        <span id="currentLabel" class="font-medium min-w-[140px] text-center"></span>
                        <button id="nextBtn" class="text-lg p-2 rounded hover:bg-indigo-100" title="Mes/Semana siguiente"><i class="ph-arrow-right"></i></button>
                    </div>
                </div>
                <div id="calendarGrid" class="grid grid-cols-7 gap-2"></div>
            </section>

            <!-- Reportes -->
            <section id="reports" class="section fade-in hidden">
                <h2 class="text-xl font-semibold mb-4">Reportes</h2>
                <div class="space-y-4">
                    <button id="exportExcel" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg shadow flex items-center gap-2">
                        <i class="ph-file-xls"></i> Exportar a Excel
                    </button>
                    <button id="exportPdf" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg shadow flex items-center gap-2">
                        <i class="ph-file-pdf"></i> Exportar a PDF
                    </button>
                    <div>
                        <label class="block mb-1 font-medium">Filtrar por Fecha</label>
                        <div class="flex gap-2 flex-wrap">
                            <input type="date" id="filterDateFrom" class="border rounded px-2 py-1" />
                            <input type="date" id="filterDateTo" class="border rounded px-2 py-1" />
                            <button id="applyFilter" class="bg-slate-600 hover:bg-slate-700 text-white px-3 py-1 rounded-lg">Aplicar filtro</button>
                            <button id="clearFilter" class="bg-gray-400 hover:bg-gray-500 text-white px-3 py-1 rounded-lg">Limpiar filtro</button>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Gestión de Citas -->
            <section id="profile" class="section fade-in hidden max-w-6xl mx-auto space-y-6">
                <h2 class="text-xl font-semibold mb-4">Gestión de Citas</h2>
                
                <!-- Formulario para agregar cita como admin -->
                <form id="formAddAppointment" class="bg-white shadow rounded-xl p-6 space-y-4">
                    <h3 class="font-semibold text-lg">Agregar nueva cita</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block font-medium mb-1" for="patientName">Nombre completo</label>
                            <input type="text" id="patientName" class="border rounded px-3 py-2 w-full" required />
                        </div>
                        <div>
                            <label class="block font-medium mb-1" for="patientPhone">Teléfono</label>
                            <input type="tel" id="patientPhone" class="border rounded px-3 py-2 w-full" pattern="[0-9+\-\s]{7,15}" placeholder="Ej: 222-123-4567" required />
                        </div>
                        <div>
                            <label class="block font-medium mb-1" for="patientWeight">Peso (kg)</label>
                            <input type="number" id="patientWeight" class="border rounded px-3 py-2 w-full" min="1" max="300" required />
                        </div>
                        <div>
                            <label class="block font-medium mb-1" for="patientEmail">Correo electrónico</label>
                            <input type="email" id="patientEmail" class="border rounded px-3 py-2 w-full" required />
                        </div>
                        <div class="md:col-span-2">
                            <label class="block font-medium mb-1" for="appointmentDate">Fecha y hora de la cita</label>
                            <input type="datetime-local" id="appointmentDate" class="border rounded px-3 py-2 w-full" required />
                        </div>
                        <div class="md:col-span-2">
                            <label class="block font-medium mb-1" for="notes">Motivo / Notas</label>
                            <textarea id="notes" class="border rounded px-3 py-2 w-full" rows="3" placeholder="Ej: Control general, consulta, etc." ></textarea>
                        </div>
                    </div>
                    <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-2 rounded-lg shadow">Agregar cita</button>
                </form>

                <!-- Lista de citas con estados -->
                <div class="bg-white shadow rounded-xl p-6">
                    <h3 class="font-semibold text-lg mb-4">Lista de citas</h3>
                    <table class="min-w-full table-auto border-collapse border border-gray-300">
                        <thead>
                            <tr class="bg-indigo-100 text-left">
                                <th class="border border-gray-300 px-3 py-1">Paciente</th>
                                <th class="border border-gray-300 px-3 py-1">Teléfono</th>
                                <th class="border border-gray-300 px-3 py-1">Fecha y hora</th>
                                <th class="border border-gray-300 px-3 py-1">Peso (kg)</th>
                                <th class="border border-gray-300 px-3 py-1">Notas</th>
                                <th class="border border-gray-300 px-3 py-1 text-center">Estado</th>
                                <th class="border border-gray-300 px-3 py-1 text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="appointmentsList"></tbody>
                    </table>
                </div>
            </section>

            <!-- Configurar Servicio -->
            <section id="serviceSettings" class="section fade-in hidden max-w-4xl mx-auto space-y-6">
                <h2 class="text-xl font-semibold mb-4">Configurar Servicio</h2>

                <form id="formBlockDay" class="bg-white shadow rounded-xl p-6 space-y-4">
                    <h3 class="font-semibold text-lg">Bloquear día sin servicio</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block font-medium mb-1" for="blockDate">Fecha a bloquear</label>
                            <input type="date" id="blockDate" class="border rounded px-3 py-2 w-full" required />
                        </div>
                        <div>
                            <label class="block font-medium mb-1" for="openHour">Hora de apertura</label>
                            <input type="time" id="openHour" class="border rounded px-3 py-2 w-full" value="08:00" required />
                        </div>
                        <div>
                            <label class="block font-medium mb-1" for="closeHour">Hora de cierre</label>
                            <input type="time" id="closeHour" class="border rounded px-3 py-2 w-full" value="17:00" required />
                        </div>
                    </div>
                    <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-6 py-2 rounded-lg shadow">Bloquear día</button>
                </form>

                <div class="bg-white shadow rounded-xl p-6">
                    <h3 class="font-semibold text-lg mb-4">Días bloqueados sin servicio</h3>
                    <table class="min-w-full table-auto border-collapse border border-gray-300">
                        <thead>
                            <tr class="bg-red-100 text-left">
                                <th class="border border-gray-300 px-3 py-1">Fecha</th>
                                <th class="border border-gray-300 px-3 py-1">Hora apertura</th>
                                <th class="border border-gray-300 px-3 py-1">Hora cierre</th>
                                <th class="border border-gray-300 px-3 py-1 text-center">Acción</th>
                            </tr>
                        </thead>
                        <tbody id="blockedDaysList"></tbody>
                    </table>
                </div>
            </section>

        </main>
    </div>

    <!-- Librerías para exportar -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>

    <script src="../assets/js/main.js"></script>
    <script>
        // Cargar el JavaScript principal
        document.addEventListener('DOMContentLoaded', function() {
            // Inicializar el dashboard
            initDashboard();
        });
    </script>

    <!-- jsPDF AutoTable plugin -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.25/jspdf.plugin.autotable.min.js"></script>
</body>
</html>
