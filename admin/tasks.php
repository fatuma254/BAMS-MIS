<?php 
ob_start();
      include('../includes/topbar.php');
      include('../includes/sidebar.php');
      require_once '../includes/db_connection.php';


// Function to get all tasks
function getTasks($conn) {
    $stmt = $conn->prepare("SELECT * FROM tasks ORDER BY start_date, start_time");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Function to add new task
function addTask($conn, $data) {
    $sql = "INSERT INTO tasks (title, description, start_date, start_time, end_date, end_time, priority, status, assigned_to, reminder_before) 
            VALUES (:title, :description, :start_date, :start_time, :end_date, :end_time, :priority, :status, :assigned_to, :reminder_before)";
    
    $stmt = $conn->prepare($sql);
    $stmt->execute($data);
    
    $taskId = $conn->lastInsertId();
    
    // Create reminder
    $reminderTime = date('Y-m-d H:i:s', strtotime("$data[start_date] $data[start_time] - $data[reminder_before] minutes"));
    $reminderSql = "INSERT INTO task_reminders (task_id, reminder_time) VALUES (:task_id, :reminder_time)";
    $reminderStmt = $conn->prepare($reminderSql);
    $reminderStmt->execute(['task_id' => $taskId, 'reminder_time' => $reminderTime]);
    
    return $taskId;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Maraba Farm - Task Management</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link href='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.css' rel='stylesheet'>
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.0.19/dist/sweetalert2.min.css" rel="stylesheet">
</head>
<style>
    /* styles.css */
.fc-event {
    cursor: pointer;
    padding: 2px 5px;
    border-radius: 3px;
    margin-bottom: 2px;
}

.task-priority-high {
    border-left: 4px solid #dc3545 !important;
}

.task-priority-medium {
    border-left: 4px solid #ffc107 !important;
}

.task-priority-low {
    border-left: 4px solid #28a745 !important;
}

.task-completed {
    opacity: 0.7;
    text-decoration: line-through;
}

.reminder-toast {
    position: fixed;
    top: 20px;
    right: 20px;
    z-index: 1050;
}

/* Custom styles for the calendar */
.fc-day-today {
    background-color: rgba(var(--bs-primary-rgb), 0.1) !important;
}

.fc-event-time {
    font-weight: bold;
}

/* Modal customizations */
.modal-header {
    background-color: var(--bs-primary);
    color: white;
}

.task-details {
    background-color: #f8f9fa;
    padding: 15px;
    border-radius: 5px;
    margin-bottom: 15px;
}



.task-list-container {
    max-height: 600px;
    overflow-y: auto;
}

.list-group-item {
    margin-bottom: 8px;
    border-radius: 4px !important;
    border-left-width: 4px !important;
}

.task-priority-high {
    border-left-color: #dc3545 !important;
    
}

.task-priority-medium {
    border-left-color: #ffc107 !important;
}

.task-priority-low {
    border-left-color: #28a745 !important;
}

.fc-event {
    cursor: pointer;
    padding: 2px 5px;
    margin-bottom: 2px;
    color:#000 !important;
}

.calendar-container {
    background-color: #dc3545;
    height: 700px;
}

#calendar {
    height: 100%;
    color:black;

}

.task-details {
    text-align: left;
    padding: 15px;
    background-color: #f8f9fa;
    border-radius: 5px;
    margin: 10px 0;
    
}

.task-details p {
    margin-bottom: 8px;
}


.fc-toolbar {
            background-color: black;
            color: white;
        }

        /* Style the buttons to be transparent with white text */
        .fc-toolbar-chunk .fc-button {
            background-color: transparent !important;
            border: none;
            color: white !important;
            opacity: 0.7;
            transition: opacity 0.3s ease;
        }

        /* Hover effect for buttons */
        .fc-toolbar-chunk .fc-button:hover {
            opacity: 1;
            background-color: rgba(255, 255, 255, 0.2) !important;
        }

        /* Active/pressed button state */
        .fc-toolbar-chunk .fc-button-active {
            opacity: 1;
            background-color: rgba(255, 255, 255, 0.3) !important;
        }
</style>
<body>
<div class="content-wrapper pb-0">

<div class="container-fluid mt-4">
    <div class="row">
        <!-- Task List Section -->
        <div class="col-md-4">
    <div class="card">
        <div class="card-header">
            <h4 class="mb-0">Task Management</h4>
        </div>
        <div class="card-body">
            <button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#addTaskModal">
                <i class="fas fa-plus"></i> Add New Task
            </button>
            <div class="task-list-container">
                <div id="taskList" class="list-group">
                    <!-- Tasks will be loaded here dynamically -->
                </div>
            </div>
        </div>
    </div>
</div>
        
        <!-- Calendar Section -->
        <div class="col-md-8">
            <div class="card">
                <div class="card-body ">
                    <div id="calendar"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Task Modal -->
<div class="modal fade" id="addTaskModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Task</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="addTaskForm">
                    <div class="mb-3">
                        <label class="form-label">Title</label>
                        <input type="text" class="form-control" name="title" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" name="description"></textarea>
                    </div>
                    <div class="row mb-3">
                        <div class="col">
                            <label class="form-label">Start Date</label>
                            <input type="date" class="form-control" name="start_date" required>
                        </div>
                        <div class="col">
                            <label class="form-label">Start Time</label>
                            <input type="time" class="form-control" name="start_time" required>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col">
                            <label class="form-label">End Date</label>
                            <input type="date" class="form-control" name="end_date">
                        </div>
                        <div class="col">
                            <label class="form-label">End Time</label>
                            <input type="time" class="form-control" name="end_time">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Priority</label>
                        <select class="form-select" name="priority">
                            <option value="Low">Low</option>
                            <option value="Medium" selected>Medium</option>
                            <option value="High">High</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Reminder Before (minutes)</label>
                        <input type="number" class="form-control" name="reminder_before" value="30" min="5">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="saveTask">Save Task</button>
            </div>
        </div>
    </div>
</div>

<!-- Edit Task Modal -->
<div class="modal fade" id="editTaskModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Task</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="editTaskForm">
                    <input type="hidden" name="task_id" id="edit_task_id">
                    <div class="mb-3">
                        <label class="form-label">Title</label>
                        <input type="text" class="form-control" name="title" id="edit_title" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" name="description" id="edit_description"></textarea>
                    </div>
                    <div class="row mb-3">
                        <div class="col">
                            <label class="form-label">Start Date</label>
                            <input type="date" class="form-control" name="start_date" id="edit_start_date" required>
                        </div>
                        <div class="col">
                            <label class="form-label">Start Time</label>
                            <input type="time" class="form-control" name="start_time" id="edit_start_time" required>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col">
                            <label class="form-label">End Date</label>
                            <input type="date" class="form-control" name="end_date" id="edit_end_date">
                        </div>
                        <div class="col">
                            <label class="form-label">End Time</label>
                            <input type="time" class="form-control" name="end_time" id="edit_end_time">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Priority</label>
                        <select class="form-select" name="priority" id="edit_priority">
                            <option value="Low">Low</option>
                            <option value="Medium">Medium</option>
                            <option value="High">High</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select class="form-select" name="status" id="edit_status">
                            <option value="Pending">Pending</option>
                            <option value="In Progress">In Progress</option>
                            <option value="Completed">Completed</option>
                            <option value="Cancelled">Cancelled</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Reminder Before (minutes)</label>
                        <input type="number" class="form-control" name="reminder_before" id="edit_reminder_before" value="30" min="5">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="updateTask">Update Task</button>
            </div>
        </div>
    </div>
</div>

</div>
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.0.19/dist/sweetalert2.min.js"></script>
<script src="task-manager.js"></script>

</body>
</html>
<script>
// Create audio context for reminder sound
const audioContext = new (window.AudioContext || window.webkitAudioContext)();

// Task Manager Module
const TaskManager = {
    // Function to edit task with improved error handling
    editTask: function(taskId) {
        // Show loading state
        Swal.fire({
            title: 'Loading...',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        fetch(`get_task.php?id=${taskId}`, {
            method: 'GET',
            headers: {
                'Accept': 'application/json'
            }
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            Swal.close();
            if (data.success && data.task) {
                const task = data.task;
                // Populate edit form with null checks
                document.getElementById('edit_task_id').value = task.task_id || '';
                document.getElementById('edit_title').value = task.title || '';
                document.getElementById('edit_description').value = task.description || '';
                document.getElementById('edit_start_date').value = task.start_date || '';
                document.getElementById('edit_start_time').value = task.start_time || '';
                document.getElementById('edit_end_date').value = task.end_date || '';
                document.getElementById('edit_end_time').value = task.end_time || '';
                document.getElementById('edit_priority').value = task.priority || 'Medium';
                document.getElementById('edit_status').value = task.status || 'Pending';
                document.getElementById('edit_reminder_before').value = task.reminder_before || 30;

                // Show edit modal
                const editModal = new bootstrap.Modal(document.getElementById('editTaskModal'));
                editModal.show();
            } else {
                throw new Error(data.message || 'Failed to load task details');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: `Failed to load task details: ${error.message}`,
                confirmButtonText: 'OK'
            });
        });
    },

    // Function to delete task with improved error handling
    deleteTask: function(taskId) {
        Swal.fire({
            title: 'Are you sure?',
            text: "You won't be able to revert this!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                // Show loading state
                Swal.fire({
                    title: 'Deleting...',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                const formData = new FormData();
                formData.append('id', taskId);

                fetch('delete_task.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Deleted!',
                            text: 'Task has been deleted successfully.',
                            confirmButtonText: 'OK'
                        }).then(() => {
                            location.reload();
                        });
                    } else {
                        throw new Error(data.message || 'Failed to delete task');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: `Failed to delete task: ${error.message}`,
                        confirmButtonText: 'OK'
                    });
                });
            }
        });
    },

    // Initialize event handlers with improved error handling
    init: function() {
        // Update task button handler with validation
        document.getElementById('updateTask').addEventListener('click', function() {
            const form = document.getElementById('editTaskForm');
            
            // Basic form validation
            if (!form.checkValidity()) {
                form.reportValidity();
                return;
            }

            const formData = new FormData(form);

            // Show loading state
            Swal.fire({
                title: 'Updating...',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            fetch('update_task.php', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    const modal = bootstrap.Modal.getInstance(document.getElementById('editTaskModal'));
                    modal.hide();
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: 'Task updated successfully!',
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    throw new Error(data.message || 'Failed to update task');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: `Failed to update task: ${error.message}`,
                    confirmButtonText: 'OK'
                });
            });
        });
    },

    // Function to play reminder sound
    playReminderSound:function() {
    const audioContext = new (window.AudioContext || window.webkitAudioContext)();
    const oscillator = audioContext.createOscillator();
    const gainNode = audioContext.createGain();

    oscillator.connect(gainNode);
    gainNode.connect(audioContext.destination);

    oscillator.type = 'sine';
    oscillator.frequency.value = 440;
    gainNode.gain.value = 0.5;

    oscillator.start();
    setTimeout(() => oscillator.stop(), 1000);
},

    // Function to check and update task status based on dates
    updateTaskStatus: function() {
        const now = new Date();
        
        fetch('get_tasks.php')
            .then(response => response.json())
            .then(tasks => {
                tasks.forEach(task => {
                    const startDateTime = new Date(task.start);
                    const endDateTime = task.end ? new Date(task.end) : null;
                    let newStatus = task.status;

                    if (task.status !== 'Completed' && task.status !== 'Cancelled') {
                        if (startDateTime <= now) {
                            newStatus = 'In Progress';
                        }
                        if (endDateTime && endDateTime <= now) {
                            newStatus = 'Completed';
                        }

                        if (newStatus !== task.status) {
                            // Update task status
                            const formData = new FormData();
                            formData.append('task_id', task.id);
                            formData.append('status', newStatus);
                            
                            fetch('update_task.php', {
                                method: 'POST',
                                body: formData
                            });
                        }
                    }
                });
            });
    },

    // Function to handle reminders
    checkReminders: function() {
    fetch('check_reminders.php')
        .then(response => response.json())
        .then(reminders => {
            reminders.forEach(reminder => {
                if (!reminder.error) {
                    playReminderSound();
                    Swal.fire({
                        title: 'Task Reminder',
                        text: `Task "${reminder.title}" starts in ${reminder.reminder_before} minutes!`,
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Acknowledge',
                        cancelButtonText: 'Snooze (5 min)',
                        allowOutsideClick: false
                    }).then((result) => {
                        if (result.isConfirmed) {
                            // Update task status to In Progress
                            const formData = new FormData();
                            formData.append('task_id', reminder.task_id);
                            formData.append('status', 'In Progress');

                            fetch('update_task.php', {
                                method: 'POST',
                                body: formData
                            });
                        } else if (result.dismiss === Swal.DismissReason.cancel) {
                            // Snooze reminder for 5 minutes
                            const formData = new FormData();
                            formData.append('task_id', reminder.task_id);
                            formData.append('snooze_minutes', 5);

                            fetch('snooze_reminder.php', {
                                method: 'POST',
                                body: formData
                            });
                        }
                    });
                }
            });
        })
        .catch(error => console.error('Error checking reminders:', error));
},
    

    // Initialize event handlers
    init: function() {
        // Update task button handler
        document.getElementById('updateTask').addEventListener('click', function() {
            const form = document.getElementById('editTaskForm');
            const formData = new FormData(form);

            fetch('update_task.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const modal = bootstrap.Modal.getInstance(document.getElementById('editTaskModal'));
                    modal.hide();
                    Swal.fire({
                        title: 'Success!',
                        text: 'Task updated successfully!',
                        icon: 'success',
                        timer: 2000
                    }).then(() => {
                        location.reload(); // Reload page after update
                    });
                } else {
                    Swal.fire('Error', data.message || 'Failed to update task', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire('Error', 'Failed to update task', 'error');
            });
        });

        // Check and update task statuses every minute
        setInterval(() => this.updateTaskStatus(), 60000);
        
        // Check for reminders every 30 seconds
        setInterval(() => this.checkReminders(), 30000);
    }
};

// Initialize TaskManager when document is ready
document.addEventListener('DOMContentLoaded', function() {
    TaskManager.init();
    
    // Make editTask and deleteTask functions globally available
    window.editTask = TaskManager.editTask;
    window.deleteTask = TaskManager.deleteTask;
});











document.addEventListener('DOMContentLoaded', function() {
    // Initialize FullCalendar
    const calendarEl = document.getElementById('calendar');
    const calendar = new FullCalendar.Calendar(calendarEl, {
    initialView: 'dayGridMonth',
    headerToolbar: {
        left: 'prev,next today',
        center: 'title',
        right: 'dayGridMonth,timeGridWeek,timeGridDay'
    },
    // Change this from a string to a function
    events: function(info, successCallback, failureCallback) {
        fetch('get_tasks.php')
            .then(response => response.json())
            .then(data => {
                if (Array.isArray(data)) {
                    successCallback(data);
                } else if (data.error) {
                    failureCallback(data.error);
                }
            })
            .catch(error => {
                failureCallback(error);
            });
    },
    eventClick: function(info) {
        showTaskDetails(info.event);
    },
    eventDidMount: function(info) {
        // Color coding based on priority
        switch(info.event.extendedProps.priority) {
            case 'High':
                info.el.classList.add('task-priority-high');
                break;
            case 'Medium':
                info.el.classList.add('task-priority-medium');
                break;
            case 'Low':
                info.el.classList.add('task-priority-low');
                break;
        }
    }
});
    calendar.render();

    // Load tasks initially
    loadTasks();

    // Check for reminders every minute
    setInterval(checkReminders, 60000);

    // Add Task Form Handler
    document.getElementById('addTaskForm').addEventListener('submit', function(e) {
        e.preventDefault();
    });
// Save Task Handler
document.getElementById('saveTask').addEventListener('click', function() {
    const form = document.getElementById('addTaskForm');
    const formData = new FormData(form);

    fetch('save_task.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const modal = bootstrap.Modal.getInstance(document.getElementById('addTaskModal'));
            modal.hide();
            form.reset();
            Swal.fire({
                title: 'Success!',
                text: 'Task saved successfully!',
                icon: 'success',
                timer: 2000
            }).then(() => {
                loadTasks();
                calendar.refetchEvents();
                location.reload();
            });
        } else {
            Swal.fire('Error', data.message || 'Failed to save task', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        Swal.fire('Error', 'Failed to save task', 'error');
    });
});
});

function loadTasks() {
    fetch('get_tasks.php')
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            if (data.error) {
                throw new Error(data.error);
            }
            
            const taskList = document.getElementById('taskList');
            taskList.innerHTML = ''; // Clear existing tasks
            
            if (!Array.isArray(data) || data.length === 0) {
                taskList.innerHTML = '<div class="list-group-item">No tasks found</div>';
                return;
            }
            
            data.forEach(task => {
                const taskElement = createTaskElement(task);
                taskList.appendChild(taskElement);
            });
        })
        .catch(error => {
            console.error('Error loading tasks:', error);
            Swal.fire({
                title: 'Error',
                text: 'Failed to load tasks: ' + error.message,
                icon: 'error'
            });
            document.getElementById('taskList').innerHTML = 
                '<div class="list-group-item text-danger">Error loading tasks. Please try again later.</div>';
        });
}
function createTaskElement(task) {
    const startDateTime = new Date(task.start);
    const endDateTime = task.end ? new Date(task.end) : null;
    
    const div = document.createElement('div');
    div.className = `list-group-item task-priority-${task.priority.toLowerCase()}`;
    
    const statusBadgeColor = getStatusBadgeColor(task.status);
    const priorityBadgeColor = getPriorityBadgeColor(task.priority);

    div.innerHTML = `
        <div class="d-flex w-100 justify-content-between">
            <h5 class="mb-1">${escapeHtml(task.title)}</h5>
            <small>
                <span class="badge bg-${priorityBadgeColor}">${task.priority}</span>
                <span class="badge bg-${statusBadgeColor}">${task.status || 'Pending'}</span>
            </small>
        </div>
        <p class="mb-1">${escapeHtml(task.description || 'No description')}</p>
        <small>
            Start: ${startDateTime.toLocaleString()}
            ${endDateTime ? `<br>End: ${endDateTime.toLocaleString()}` : ''}
        </small>
        <div class="mt-2">
            <button class="btn btn-sm btn-primary" onclick="editTask(${task.task_id})">
                <i class="fas fa-edit"></i> Edit
            </button>
            <button class="btn btn-sm btn-danger" onclick="deleteTask(${task.task_id})">
                <i class="fas fa-trash"></i> Delete
            </button>
        </div>
    `;
    return div;
}

function getStatusBadgeColor(status) {
    switch(status) {
        case 'Completed': return 'success';
        case 'In Progress': return 'primary';
        case 'Pending': return 'warning';
        case 'Cancelled': return 'danger';
        default: return 'secondary';
    }
}

function getPriorityBadgeColor(priority) {
    switch(priority) {
        case 'High': return 'danger';
        case 'Medium': return 'warning';
        case 'Low': return 'success';
        default: return 'secondary';
    }
}

function escapeHtml(unsafe) {
    return unsafe
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
}

function checkReminders() {
    fetch('check_reminders.php')
        .then(response => response.json())
        .then(reminders => {
            reminders.forEach(reminder => {
                if (!reminder.error) {
                    Swal.fire({
                        title: 'Task Reminder',
                        text: `Task "${reminder.title}" starts in ${reminder.reminder_before} minutes!`,
                        icon: 'info',
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 5000,
                        timerProgressBar: true
                    });
                }
            });
        })
        .catch(error => console.error('Error checking reminders:', error));
}

function showTaskDetails(event) {
    const startDate = event.start ? event.start.toLocaleString() : 'Not set';
    const endDate = event.end ? event.end.toLocaleString() : 'Not set';
    
    Swal.fire({
        title: event.title,
        html: `
            <div class="task-details">
                <p><strong>Description:</strong> ${event.extendedProps.description || 'No description'}</p>
                <p><strong>Start:</strong> ${startDate}</p>
                <p><strong>End:</strong> ${endDate}</p>
                <p><strong>Priority:</strong> ${event.extendedProps.priority}</p>
                <p><strong>Status:</strong> ${event.extendedProps.status || 'Pending'}</p>
            </div>
        `,
        showCancelButton: true,
        confirmButtonText: 'Edit',
        cancelButtonText: 'Close'
    }).then((result) => {
        if (result.isConfirmed) {
            editTask(event.id);
        }
    });
}







</script>

<?php include('../includes/footer.php');?>