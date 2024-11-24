const hostName = window.location.hostname;
const hostNameServerUrl = '/task/';

$(document).ready(function() {
	// Global Settings
	let edit = false;
	let selectedDate = null;
	
	// Testing Jquery
	console.log('jquery is working!');
	fetchTasks();
	$('#task-result').hide();

	var url = window.location.href;
	var searchValue = new URL(url).searchParams.get("search");

	$(function(){
		if(searchValue){
			$('#search').val(searchValue).trigger("keyup");
		}
	});
	
	// search key type event
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
						
						if (tasks.length === 0) {
							template = '<li class="list-group-item">검색 결과가 없습니다.</li>';
						} else {
							tasks.forEach(task => {
								const dateObj = new Date(task.due_datetime);
								const formattedDate = dateObj.toLocaleDateString('ko-KR', {
									year: 'numeric',
									month: '2-digit',
									day: '2-digit'
								});
								const formattedTime = dateObj.toLocaleTimeString('ko-KR', {
									hour: '2-digit',
									minute: '2-digit'
								});
								
								const statusBadge = task.task_status === 'completed' 
									? '<span class="badge badge-success">완료</span>'
									: '<span class="badge badge-primary">진행중</span>';
									
								template += `
								<li class="list-group-item" taskId="${task.id}">
									<a href="#" class="task-item d-flex justify-content-between align-items-center">
										<div>
											<div class="d-flex align-items-center gap-2">
												<strong>${task.name}</strong>
												${statusBadge}
											</div>
											<small class="text-muted">${task.description}</small>
										</div>
										<div class="text-muted">
											<div>${formattedDate}</div>
											<div>${formattedTime}</div>
										</div>
									</a>
								</li>`;
							});
						}
						
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
			success: function(tasks) {
				if(tasks.result == "ok") {
					const list = tasks.tasks;
					let template = '';
					$('#tasks').html(template);
					$('.user-name').remove();
				} else {
					alert(tasks.msg);
					window.location.href = "/login.html";
				}
			}
		});
	}

	// task form reset
	function initForm(){
		edit = false;
		$('#name').val("");
		$('#description').val("");
		$('#taskId').val("");
		$('#due_date').val("");
		$('#task-result').hide();		
	}
	// Get a Single Task by Id 
	$(document).on('click', '.task-item', function(e) {
		e.preventDefault();
		let id;
		const $element = $(this);
		
		// 검색 결과에서 클릭한 경우
		if ($element.closest('.list-group-item').length) {
			id = $element.closest('.list-group-item').attr('taskId');
		}
		// 다른 리스트에서 클릭한 경우
		else if ($element.closest('tr').length) {
			id = $element.closest('tr').attr('taskId');
		}
		// Today's Tasks에서 클릭한 경우
		else {
			id = $element.attr('taskId');
		}
	
		if (id) {
			$.ajax({
				url: hostNameServerUrl + 'task-single.php',
				type: 'POST',
				data: { id: id },
				dataType: 'json',
				success: function(response) {
					if (response.error) {
						alert(response.message);
						return;
					}
					
					$('#name').val(response.name);
					$('#description').val(response.description);
					$('#taskId').val(response.id);
					
					if (response.due_datetime) {
						const dueDate = new Date(response.due_datetime);
						const formattedDate = dueDate.getFullYear() + '-' + 
							String(dueDate.getMonth() + 1).padStart(2, '0') + '-' + 
							String(dueDate.getDate()).padStart(2, '0') + 'T' + 
							String(dueDate.getHours()).padStart(2, '0') + ':' + 
							String(dueDate.getMinutes()).padStart(2, '0');
						
						$('#due_date').val(formattedDate);
					}
					
					edit = true;
					
					// 폼으로 스크롤
					$('html, body').animate({
						scrollTop: $("#task-form").offset().top - 100
					}, 500);
				},
				error: function(xhr, status, error) {
					console.error('Error loading task:', error);
					alert('작업을 불러오는데 실패했습니다.');
				}
			});
		}
	});
	
	// Delete a Single Task
	$(document).on('click', '.task-delete', (e) => {
		if (confirm('Are you sure you want to delete it?')) {
			const element = $(this)[0].activeElement.parentElement.parentElement;
			const id = $(element).attr('taskId');
			$.post(hostNameServerUrl+'task-delete.php', { id }, (response) => {
				if( response == 'Task Deleted Successfully'){
					initForm();
					fetchTasks();
					$("#search").trigger("keyup");
				}
				alert(response);
			});
		}
	});

    // 특정 날짜의 작업 로드 함수
    function loadTasksByDate(date) {
		selectedDate = date; // 선택된 날짜 저장
		$.ajax({
			url: hostNameServerUrl + 'tasks-by-date.php',
			type: 'GET',
			data: { date: date },
			dataType: 'json',
			success: function(response) {
				if(response.result === 'ok') {
					const list = response.tasks;
					let template = '';
					
					if(list.length === 0) {
						template = '<tr><td colspan="4" class="text-center">이 날짜에는 등록된 작업이 없습니다.</td></tr>';
					} else {
						list.forEach(task => {
							const dueTime = new Date(task.due_datetime).toLocaleTimeString('ko-KR', {
								hour: '2-digit',
								minute: '2-digit'
							});
							const statusBadge = task.task_status === 'completed' 
								? '<span class="badge badge-success">완료</span>'
								: '<span class="badge badge-primary">진행중</span>';
							
							template += `
							<tr taskId="${task.id}">
								<td>
									<a href="#" class="task-item">
										${task.name}
										${statusBadge}
									</a>
								</td>
								<td>${dueTime}까지</td>
								<td>${task.description}</td>
								<td>
									<button class="task-delete btn btn-danger btn-sm" data-taskId="${task.id}">삭제</button>
									<button class="btn btn-sm btn-toggle-status" data-id="${task.id}" 
										data-status="${task.task_status === 'completed' ? 'pending' : 'completed'}">
										${task.task_status === 'completed' ? '↺' : '✓'}
									</button>
								</td>
							</tr>`;
						});
					}
					
					$('#tasks').html(template);
				} else {
					alert(response.msg);
				}
			}
		});
	}

    // 선택된 날짜 표시 업데이트
    function updateSelectedDateDisplay(date) {
        const formattedDate = date.format('YYYY년 MM월 DD일');
        $('.table-title').html(`<h5>${formattedDate}의 작업 목록</h5>`);
    }

    // "모든 작업 보기" 버튼 추가 및 이벤트 처리
    $('.table-bordered').before(`
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div class="table-title"><h5>전체 작업 목록</h5></div>
            <button id="showAllTasks" class="btn btn-outline-primary">모든 작업 보기</button>
        </div>
    `);

    $('#showAllTasks').click(function() {
		selectedDate = null; // 선택된 날짜 초기화
		fetchTasks();
		$('.table-title').html('<h5>전체 작업 목록</h5>');
		$('.fc-day').removeClass('selected-date');
	});

    // 상태 토글 버튼 클릭 이벤트
	$(document).on('click', '.btn-toggle-status', function(event) {
		const id = $(this).data('id');
		const newStatus = $(this).data('status');
		const $button = $(this);
		
		$.ajax({
			url: hostNameServerUrl + 'update-status.php',
			type: 'POST',
			data: { 
				id: id, 
				status: newStatus 
			},
			success: function(response) {
				// 현재 선택된 날짜가 있으면 해당 날짜의 작업 목록을 새로고침
				if (selectedDate) {
					loadTasksByDate(selectedDate);
				} else {
					fetchTasks(); // 전체 목록 보기일 경우
				}
				
				// Today's Tasks 업데이트
				loadTodayTasks();
				
				// 캘린더 새로고침
				try {
					if ($('#calendar').length) {
						$('#calendar').fullCalendar('refetchEvents');
					}
				} catch (error) {
					console.log('Calendar refresh skipped');
				}
			},
			error: function(xhr, status, error) {
				console.error('Status update failed:', error);
				alert('상태 업데이트에 실패했습니다.');
			}
		});
	});

    // task-form submit 이벤트 
	$('#task-form').submit(function(e) {
		e.preventDefault();
		
		const dueDateTime = new Date($('#due_date').val());
		const now = new Date();
		
		if (dueDateTime < now) {
			alert('현재 시간 이후로 날짜와 시간을 설정해주세요.');
			return;
		}
		
		const postData = {
			name: $('#name').val(),
			description: $('#description').val(),
			due_datetime: $('#due_date').val()
		};
	
		// edit 모드이고 taskId가 있을 때만
		if (edit && $('#taskId').val()) {
			postData.id = $('#taskId').val(); // id 필드 추가
		}
		
		const url = edit ? 'task-edit.php' : 'task-add.php';
		
		$.ajax({
			url: hostNameServerUrl + url,
			type: 'POST',
			data: postData,
			success: function(response) {
				if (response.includes('Successfully')) {
					$('#task-form').trigger('reset');
					$('#taskId').val(''); 
	
					edit = false;
					
					if (selectedDate) {
						loadTasksByDate(selectedDate);
					} else {
						fetchTasks();
					}
					
					if ($('#calendar').length) {
						$('#calendar').fullCalendar('refetchEvents');
					}
					
					loadTodayTasks();
					alert(response);
				} else {
					alert('작업 업데이트에 실패했습니다: ' + response);
				}
			},
			error: function(xhr, status, error) {
				console.error('Update failed:', error);
				alert('작업 업데이트에 실패했습니다. 다시 시도해주세요.');
			}
		});
	});
	function updateMinDateTime() {
        const now = new Date();
        const year = now.getFullYear();
        const month = String(now.getMonth() + 1).padStart(2, '0');
        const day = String(now.getDate()).padStart(2, '0');
        const hours = String(now.getHours()).padStart(2, '0');
        const minutes = String(now.getMinutes()).padStart(2, '0');
        
        const minDateTime = `${year}-${month}-${day}T${hours}:${minutes}`;
        $('#due_date').attr('min', minDateTime);
    }
    
    // 페이지 로드시와 input 포커스시 최소값 업데이트
    updateMinDateTime();
    $('#due_date').on('focus', updateMinDateTime);
    
    $('#calendar').fullCalendar({
        header: {
            left: 'prev,next today',
            center: 'title',
            right: 'month'
        },
        defaultView: 'month',
        events: hostNameServerUrl + 'calendar-events.php',
        eventRender: function(event, element) {
            const statusBadge = event.status === 'completed' 
                ? '<span class="badge badge-success">완료</span>'
                : '<span class="badge badge-primary">진행중</span>';
            element.find('.fc-title').append(statusBadge);
        },
        eventClick: function(event) {  // 이벤트 클릭 핸들러 추가
            $.ajax({
                url: hostNameServerUrl + 'task-single.php',
                type: 'POST',
                data: { id: event.id },
                success: function(response) {
                    const task = JSON.parse(response);
                    
                    $('#name').val(task.name);
                    $('#description').val(task.description);
                    $('#taskId').val(task.id);
                    
                    if(task.due_datetime) {
                        const dueDate = new Date(task.due_datetime);
                        const formattedDate = dueDate.getFullYear() + '-' + 
                            String(dueDate.getMonth() + 1).padStart(2, '0') + '-' + 
                            String(dueDate.getDate()).padStart(2, '0') + 'T' + 
                            String(dueDate.getHours()).padStart(2, '0') + ':' + 
                            String(dueDate.getMinutes()).padStart(2, '0');
                        
                        $('#due_date').val(formattedDate);
                    }
                    
                    edit = true;
                    
                    // 스크롤을 폼으로 이동
                    $('html, body').animate({
                        scrollTop: $("#task-form").offset().top
                    }, 500);
                },
                error: function(xhr, status, error) {
                    console.error('Error loading task:', error);
                    alert('작업을 불러오는데 실패했습니다.');
                }
            });
        },
        dayClick: function(date) {
            selectedDate = date.format();
            loadTasksByDate(selectedDate);
            $('.fc-day').removeClass('selected-date');
            $(this).addClass('selected-date');
            updateSelectedDateDisplay(date);
        }
    });
	function loadTodayTasks() {
		$.ajax({
			url: hostNameServerUrl + 'tasks-today.php',
			type: 'GET',
			dataType: 'json',
			success: function(tasks) {
				let template = '';
				
				if (tasks.length === 0) {
					template = '<div class="alert alert-info">오늘 예정된 작업이 없습니다.</div>';
				} else {
					template = '<div class="list-group">';
					tasks.forEach(task => {
						const dueTime = new Date(task.due_datetime).toLocaleTimeString('ko-KR', {
							hour: '2-digit',
							minute: '2-digit'
						});
						
						const statusClass = task.task_status === 'completed' ? 'list-group-item-success' : 'list-group-item-primary';
						const statusIcon = task.task_status === 'completed' ? '✓' : '⋯';
						
						template += `
							<div class="list-group-item ${statusClass} d-flex justify-content-between align-items-center">
								<a href="#" class="task-item" style="text-decoration: none; color: inherit;" taskId="${task.id}">
									<div>
										<h6 class="mb-1">${task.name}</h6>
										<small>${dueTime}</small>
									</div>
								</a>
								<div>
									<button class="btn btn-sm btn-toggle-status" 
										data-id="${task.id}" 
										data-status="${task.task_status === 'completed' ? 'pending' : 'completed'}">
										${statusIcon}
									</button>
								</div>
							</div>
						`;
					});
					template += '</div>';
				}
				
				$('#today-tasks').html(template);
			},
			error: function(xhr, status, error) {
				console.error('Error loading today\'s tasks:', error);
				$('#today-tasks').html('<div class="alert alert-danger">작업을 불러오는데 실패했습니다.</div>');
			}
		});
	}
	 // 페이지 로드시 오늘의 할일 로드
	 loadTodayTasks();

	 // 30초마다 오늘의 할일 새로고침
	 setInterval(loadTodayTasks, 30000);
});

