const hostName = window.location.hostname;
const hostNameServerUrl = '/task/';

$(document).ready(function() {
    console.log('jquery is working!');
    fetchTasks();
    $('#task-result').hide();

    // 로그아웃 버튼 이벤트
    $('#logoutBtn').click(function() {
        $.ajax({
            url: hostNameServerUrl + 'logout.php',
            type: 'GET',
            success: function(response) {
                if(response.result === "ok") {
                    window.location.href = "/login.html";
                }
            }
        });
    });

    // 검색 이벤트
    $('#search').keyup(function() {
        if ($('#search').val()) {
            let search = $('#search').val();
            $.ajax({
                url: hostNameServerUrl+'task-search.php',
                data: { search },
                type: 'POST',
                success: function(response) {
                    if (!response.error) {
                        let tasks = JSON.parse(response);
                        let template = '';
                        tasks.forEach(task => {
                            template += `
                            <li class="list-group-item">
                                ${task.name}
                                <small class="text-muted">${task.description}</small>
                            </li>`;
                        });
                        $('#task-result').show();
                        $('#container').html(template);
                    }
                }
            });
        } else {
            $('#task-result').hide();
        }
    });

    function fetchTasks() {
        $.ajax({
            url: hostNameServerUrl + 'tasks-list.php',
            type: 'GET',
            dataType: "json",
            success: function(response) {
                
                if(response.result == "ok") {
                    const grouped_tasks = response.grouped_tasks;
                    let template = '';
                    
                    grouped_tasks.forEach(month_group => {
                        template += `
                        <div class="month-container">
                            <div class="month-header">
                                ${month_group.formatted_month}
                            </div>`;
                        
                        month_group.days.forEach(day => {
                            // 날짜 헤더 스타일 결정
                            const today = new Date().toISOString().split('T')[0];
                            let dateClass = '';
                            if (day.date < today) {
                                dateClass = 'overdue-date';
                            } else if (day.date === today) {
                                dateClass = 'today-date';
                            }
                            
                            template += `
                            <div class="task-group">
                                <div class="date-header ${dateClass}">
                                    ${day.formatted_date}
                                </div>
                                <div class="table-responsive">
                                    <table class="table tasks-table">
                                        <tbody>`;
                            
                            day.tasks.forEach(task => {
                                const dueTime = new Date(task.due_datetime).toLocaleTimeString('ko-KR', {
                                    hour: '2-digit',
                                    minute: '2-digit'
                                });
                                
                                const statusBadge = task.task_status === 'completed' 
                                    ? '<span class="badge badge-success task-status-badge">완료</span>'
                                    : '<span class="badge badge-primary task-status-badge">진행중</span>';
                                
                                template += `
                                <tr taskId="${task.id}">
                                    <td style="width: 40%">
                                        ${task.name}
                                        ${statusBadge}
                                    </td>
                                    <td style="width: 15%">
                                        <span class="task-time">${dueTime}</span>
                                    </td>
                                    <td style="width: 30%">${task.description}</td>
                                    <td style="width: 15%">
                                        <button class="task-delete btn btn-danger btn-sm" data-taskId="${task.id}">
                                            삭제
                                        </button>
                                        <button class="btn btn-sm btn-toggle-status" data-id="${task.id}" 
                                            data-status="${task.task_status === 'completed' ? 'pending' : 'completed'}">
                                            ${task.task_status === 'completed' ? '↺' : '✓'}
                                        </button>
                                    </td>
                                </tr>`;
                            });
                            
                            template += `
                                        </tbody>
                                    </table>
                                </div>
                            </div>`;
                        });
                        
                        template += `</div>`;
                    });
                    
                    $('#tasks-container').html(template);
                } else {
                    alert(response.msg);
                    window.location.href = "/login.html";
                }
            }
        });
    }

    // 작업 삭제 이벤트
    $(document).on('click', '.task-delete', function(e) {
        if (confirm('정말 삭제하시겠습니까?')) {
            const element = $(this).closest('tr');
            const id = element.attr('taskId');
            $.post(hostNameServerUrl+'task-delete.php', { id }, function(response) {
                if(response == 'Task Deleted Successfully'){
                    fetchTasks();
                    $("#search").trigger("keyup");
                }
                alert('작업이 삭제되었습니다.');
            });
        }
    });

    // 상태 토글 이벤트
    $(document).on('click', '.btn-toggle-status', function(event) {
        const id = $(this).data('id');
        const newStatus = $(this).data('status');
        
        $.ajax({
            url: hostNameServerUrl + 'update-status.php',
            type: 'POST',
            data: { 
                id: id, 
                status: newStatus 
            },
            success: function(response) {
                fetchTasks();
            },
            error: function(xhr, status, error) {
                console.error('Status update failed:', error);
                alert('상태 업데이트에 실패했습니다.');
            }
        });
    });
});