/* empty css           */function e(){return document.querySelector(`meta[name="csrf-token"]`)?.getAttribute(`content`)||``}function t(e){return String(e??``).replace(/&/g,`&amp;`).replace(/</g,`&lt;`).replace(/>/g,`&gt;`).replace(/"/g,`&quot;`).replace(/'/g,`&#39;`)}function n(e){return e?.name||e?.display_name||e?.assignee||`Thành viên`}function r(e){if(e?.confidence_percent!=null&&e.confidence_percent!==``)return e.confidence_percent;let t=Number(e?.score);return Number.isFinite(t)?Math.round(t*100):0}function i(){let e=document.getElementById(`tp-ai-assignee-modal`);return e||(e=document.createElement(`div`),e.id=`tp-ai-assignee-modal`,e.className=`ai-modal-overlay`,e.style.display=`none`,e.innerHTML=`
        <div class="ai-modal-card">
            <div class="ai-modal-header">
                <div class="ai-modal-title">
                    <i class="fa fa-magic" style="color: #8b5cf6; margin-right: 6px;"></i>
                    <span>AI Đề Xuất Người Xử Lý Phù Hợp</span>
                </div>
                <button type="button" class="ai-modal-close" data-ai-close>&times;</button>
            </div>
            <div class="ai-modal-body">
                <p class="ai-modal-subtitle">
                    Mô hình Deep Learning phân tích yêu cầu công việc, chuyên môn và tải công việc hiện tại.
                </p>
                <div data-ai-body></div>
            </div>
        </div>
    `,document.body.appendChild(e),e.addEventListener(`click`,t=>{(t.target===e||t.target.closest(`[data-ai-close]`))&&(e.style.display=`none`)}),e)}function a(e,i){return e.length?`<div class="ai-candidate-list">${e.map(e=>{let a=String(i||``)===String(e.user_id)?` selected`:``,o=Array.isArray(e.match_reasons)?e.match_reasons.map(e=>`
                <div class="ai-reason-item">
                    <i class="fa fa-check-circle" style="color: #10b981; margin-right: 4px;"></i> ${t(e)}
                </div>
            `).join(``):``;return`
            <div class="ai-candidate-card${a}">
                <div class="ai-candidate-header">
                    <div class="ai-candidate-info">
                        <div class="ai-candidate-avatar">${t(n(e).charAt(0).toUpperCase())}</div>
                        <div>
                            <div class="ai-candidate-name">${t(n(e))}</div>
                            <div class="ai-candidate-meta">
                                <span>${t(e.job_title||`Thành viên dự án`)}</span>
                                <span class="ai-dot">•</span>
                                <span>${e.active_tasks_count??0} task đang làm</span>
                            </div>
                        </div>
                    </div>
                    <div class="ai-candidate-score-badge">${r(e)}% Phù hợp</div>
                </div>
                ${o?`<div class="ai-candidate-reasons">${o}</div>`:``}
                <div class="ai-candidate-action">
                    <button type="button" class="ai-select-btn" data-ai-pick="${e.user_id}">
                        <i class="fa fa-check" style="margin-right: 4px;"></i> Chọn người này
                    </button>
                </div>
            </div>
        `}).join(``)}</div>`:`<div class="ai-empty-state"><i class="fa fa-info-circle"></i> Chưa tìm thấy ứng viên phù hợp.</div>`}async function o(t){let n=await(await fetch(`/tasks/recommend-assignee`,{method:`POST`,headers:{"Content-Type":`application/json`,"X-CSRF-TOKEN":e(),Accept:`application/json`,"X-Requested-With":`XMLHttpRequest`},body:JSON.stringify({name:t.name||``,summary:t.name||``,description:t.description||``,project_id:t.project_id||null,issue_type:t.issue_type||`task`,priority:t.priority||`medium`,task_id:t.task_id||null})})).json();if(!n.success||!Array.isArray(n.recommendations))throw Error(`Không thể tải gợi ý AI vào lúc này.`);return n.recommendations}async function s(t,n){return(await fetch(`/tasks/${t}`,{method:`POST`,headers:{"Content-Type":`application/json`,"X-CSRF-TOKEN":e(),Accept:`application/json`,"X-Requested-With":`XMLHttpRequest`},body:JSON.stringify({_method:`PUT`,assignee_id:n})})).json()}async function c(t){if(t)try{await fetch(`/tasks/ai-feedback`,{method:`POST`,headers:{"Content-Type":`application/json`,"X-CSRF-TOKEN":e(),Accept:`application/json`,"X-Requested-With":`XMLHttpRequest`},body:JSON.stringify({recommendation_id:t,status:`accepted`})})}catch(e){console.warn(`AI feedback error:`,e)}}function l(e){let t=e.closest(`form`),n=e=>document.getElementById(e)?.value||``;return{name:e.dataset.name||n(`name`)||n(`input-task-name`),description:e.dataset.description||n(`description`)||``,project_id:e.dataset.projectId||n(`project_id`)||null,issue_type:e.dataset.issueType||n(`issue_type`)||`task`,priority:e.dataset.priority||n(`priority`)||`medium`,task_id:e.dataset.taskId||null,mode:e.dataset.aiMode||`select`,select:e.dataset.aiSelect||`#assignee_id`,form:t}}async function u(e){let t=l(e);if(!t.name&&!t.description){alert(`Hãy nhập tên hoặc mô tả công việc trước khi dùng AI đề xuất.`);return}let r=e.innerHTML;e.disabled=!0,e.innerHTML=`<i class="fa fa-spinner fa-spin"></i> AI`;try{let e=await o(t),r=i(),l=r.querySelector(`[data-ai-body]`);l.innerHTML=a(e,t.form?.querySelector(t.select)?.value||``),r.style.display=`flex`,l.querySelectorAll(`[data-ai-pick]`).forEach(i=>{i.addEventListener(`click`,async()=>{let a=i.getAttribute(`data-ai-pick`),o=e.find(e=>String(e.user_id)===String(a));if(t.mode===`save`&&t.task_id){i.disabled=!0,i.innerHTML=`<i class="fa fa-spinner fa-spin"></i> Đang lưu...`;try{if(!(await s(t.task_id,a)).success){alert(`Không thể lưu người thực hiện.`),i.disabled=!1,i.innerHTML=`<i class="fa fa-check" style="margin-right: 4px;"></i> Chọn người này`;return}await c(o?.recommendation_id),window.location.reload();return}catch(e){console.error(e),alert(`Không thể lưu người thực hiện.`),i.disabled=!1,i.innerHTML=`<i class="fa fa-check" style="margin-right: 4px;"></i> Chọn người này`;return}}let l=document.querySelector(t.select);if(l){if(![...l.options].some(e=>String(e.value)===String(a))){let e=document.createElement(`option`);e.value=a,e.textContent=`👤 ${n(o)}`,l.appendChild(e)}l.value=a,l.dispatchEvent(new Event(`change`,{bubbles:!0}))}r.style.display=`none`})})}catch(e){console.error(e),alert(e.message||`Lỗi kết nối khi gọi dịch vụ AI.`)}finally{e.disabled=!1,e.innerHTML=r}}document.addEventListener(`click`,e=>{let t=e.target.closest(`[data-ai-assignee]`);t&&(e.preventDefault(),e.stopPropagation(),u(t))});