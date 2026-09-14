<template>
  <div class="tp-welcome">
    <!-- NAVBAR -->
    <nav class="tp-welcome-navbar">
      <div class="tp-container tp-welcome-nav-inner">
        <a :href="welcomeUrl" class="tp-welcome-brand">
          <div class="tp-welcome-brand-icon">
            <i class="fa-solid fa-square-check"></i>
          </div>
          <span>TaskPilot</span>
        </a>

        <ul class="tp-welcome-nav-links">
          <li><a href="#features" class="tp-welcome-nav-link" @click.prevent="scrollToSection('features')">Tính năng</a></li>
          <li><a href="#timeline" class="tp-welcome-nav-link" @click.prevent="scrollToSection('timeline')">Gantt Timeline</a></li>
          <li><a href="#solutions" class="tp-welcome-nav-link" @click.prevent="scrollToSection('solutions')">Giải pháp</a></li>
          <li><a href="#stats" class="tp-welcome-nav-link" @click.prevent="scrollToSection('stats')">Hiệu suất</a></li>
        </ul>

        <div class="tp-welcome-nav-auth">
          <template v-if="isAuthenticated">
            <a :href="homeUrl" class="btn-tp btn-tp-primary">
              <i class="fa-solid fa-compass"></i> Mở Workspace
            </a>
          </template>
          <template v-else>
            <a :href="loginUrl" class="tp-welcome-nav-link" style="margin-right: 8px;">Đăng nhập</a>
            <a :href="registerUrl" class="btn-tp btn-tp-primary">
              Dùng thử miễn phí <i class="fa-solid fa-arrow-right"></i>
            </a>
          </template>
        </div>
      </div>
    </nav>

    <!-- HERO SECTION -->
    <section class="tp-hero">
      <div class="tp-hero-glow"></div>
      <div class="tp-container tp-hero-content">
        <div class="tp-hero-badge">
          <i class="fa-solid fa-sparkles"></i> Nền tảng quản lý dự án thế hệ mới — Powered by AI & Realtime Gantt
        </div>

        <h1 class="tp-hero-title">
          Đưa dự án từ kế hoạch đến hoàn thành với hiệu suất vượt trội.
        </h1>

        <p class="tp-hero-subtitle">
          TaskPilot kết hợp Quản lý Agile, Sơ đồ Timeline Gantt tương tác kéo thả mượt mà, Kanban Board, Sprint, Time Tracking và Cuộc họp trực tuyến trong một Workspace minh bạch duy nhất.
        </p>

        <div class="tp-hero-actions">
          <template v-if="isAuthenticated">
            <a :href="homeUrl" class="btn-tp btn-tp-primary btn-lg" style="font-size: 16px; padding: 14px 32px;">
              <i class="fa-solid fa-rocket me-1"></i> Mở Workspace của bạn
            </a>
          </template>
          <template v-else>
            <a :href="registerUrl" class="btn-tp btn-tp-primary btn-lg" style="font-size: 16px; padding: 14px 32px;">
              Bắt đầu miễn phí ngay <i class="fa-solid fa-arrow-right"></i>
            </a>
            <a :href="loginUrl" class="btn-tp btn-tp-outline btn-lg" style="font-size: 16px; padding: 14px 32px;">
              <i class="fa-solid fa-right-to-bracket"></i> Đăng nhập Demo
            </a>
          </template>
        </div>

        <!-- HERO MOCKUP INTERACTIVE PREVIEW -->
        <div class="tp-hero-mockup-wrapper" id="timeline">
          <div class="tp-hero-mockup-card">
            <div class="tp-mockup-header">
              <div class="tp-mockup-dot" style="background: #ef4444;"></div>
              <div class="tp-mockup-dot" style="background: #f59e0b;"></div>
              <div class="tp-mockup-dot" style="background: #10b981;"></div>
              <span style="font-size: 12px; color: #64748b; margin-left: 12px;">TaskPilot Workspace — Education Project (Lớp học trực tuyến) / Timeline</span>
            </div>
            <div class="tp-mockup-body">
              <!-- Sidebar mockup -->
              <div class="tp-mockup-sidebar">
                <div class="tp-mockup-sidebar-label">Danh sách Task</div>

                <div class="tp-mockup-sidebar-item">
                  ⚡ TP-20 Thiết kế giao diện
                </div>

                <div class="tp-mockup-sub-item">
                  <span>🔖 Thiết kế UX/UI</span>
                  <span class="tp-mockup-tag" style="background: #0d6efd; color: #fff;">ĐANG LÀM</span>
                </div>

                <div class="tp-mockup-sub-item">
                  <span>↪ Thiết kế trang chủ</span>
                  <span class="tp-mockup-tag" style="background: #198754; color: #fff;">HOÀN THÀNH</span>
                </div>

                <div class="tp-mockup-sidebar-item" style="margin-top: 12px;">
                  ⚡ TP-21 Xây dựng API Register/Login
                </div>
              </div>

              <!-- Gantt timeline canvas mockup -->
              <div class="tp-mockup-main">
                <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid rgba(255,255,255,0.08); padding-bottom: 10px;">
                  <span style="font-size: 13px; font-weight: 700; color: #388bff;">
                    <i class="fa-solid fa-chart-gantt me-1"></i> Interactive Gantt Timeline (60fps Drag & Drop)
                  </span>
                  <span style="font-size: 11px; background: #10b981; color: #fff; padding: 3px 10px; border-radius: 12px;">
                    Realtime Live Sync
                  </span>
                </div>

                <div style="position: relative; height: 36px; background: rgba(255,255,255,0.03); border-radius: 6px; display: flex; align-items: center;">
                  <div class="tp-mockup-timeline-bar" style="width: 45%; margin-left: 35%; background: linear-gradient(90deg, #0d6efd, #0ea5e9);">
                    🔖 TP-21 Xem giỏ hàng
                  </div>
                </div>

                <div style="position: relative; height: 36px; background: rgba(255,255,255,0.03); border-radius: 6px; display: flex; align-items: center;">
                  <div class="tp-mockup-timeline-bar" style="width: 30%; margin-left: 65%; background: linear-gradient(90deg, #10b981, #059669);">
                    ↪ TP-22 Tính tiền
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- STATS SECTION -->
    <section class="tp-stats-section" id="stats">
      <div class="tp-container">
        <div class="tp-stats-grid">
          <div class="tp-stat-card">
            <div class="tp-stat-number">10,000+</div>
            <div class="tp-stat-label">Dự án đã được quản lý thành công</div>
          </div>
          <div class="tp-stat-card">
            <div class="tp-stat-number">60 FPS</div>
            <div class="tp-stat-label">Kéo thả Gantt Timeline mượt mà</div>
          </div>
          <div class="tp-stat-card">
            <div class="tp-stat-number">99.9%</div>
            <div class="tp-stat-label">Thời gian Uptime chuẩn doanh nghiệp</div>
          </div>
          <div class="tp-stat-card">
            <div class="tp-stat-number">4.9/5 ⭐</div>
            <div class="tp-stat-label">Đánh giá độ hài lòng từ người dùng</div>
          </div>
        </div>
      </div>
    </section>

    <!-- FEATURES SECTION -->
    <section class="tp-features" id="features">
      <div class="tp-container">
        <div class="tp-section-header">
          <span class="tp-section-eyebrow">TÍNH NĂNG VƯỢT TRỘI</span>
          <h2 class="tp-section-title">Mọi công cụ bạn cần cho dự án Agile thành công.</h2>
          <p class="tp-section-subtitle">
            Loại bỏ sự phân tán dữ liệu. TaskPilot gom toàn bộ quy trình làm việc của dự án vào một điểm tập trung duy nhất.
          </p>
        </div>

        <div class="tp-features-grid">
          <div class="tp-feature-card">
            <div class="tp-feature-icon tp-icon-purple">
              <i class="fa-solid fa-chart-gantt"></i>
            </div>
            <h3 class="tp-feature-title">Gantt Chart & Timeline 60fps</h3>
            <p class="tp-feature-desc">
              Kéo thả điều chỉnh thời gian task mượt mà, theo dõi tiến độ Epic, phụ thuộc công việc và tiến độ tổng quan dự án real-time.
            </p>
          </div>

          <div class="tp-feature-card">
            <div class="tp-feature-icon tp-icon-blue">
              <i class="fa-solid fa-table-columns"></i>
            </div>
            <h3 class="tp-feature-title">Kanban Board Trực quan</h3>
            <p class="tp-feature-desc">
              Phân loại trạng thái Cần làm, Đang làm, Hoàn thành. Cập nhật tiến độ tức thì bằng kéo thả và tùy chỉnh nhãn công việc.
            </p>
          </div>

          <div class="tp-feature-card">
            <div class="tp-feature-icon tp-icon-emerald">
              <i class="fa-solid fa-bolt"></i>
            </div>
            <h3 class="tp-feature-title">Quản lý Sprints & Backlog</h3>
            <p class="tp-feature-desc">
              Lập kế hoạch Sprint chuyên nghiệp, phân bổ Story Points, theo dõi khối lượng công việc của từng thành viên trong team.
            </p>
          </div>

          <div class="tp-feature-card">
            <div class="tp-feature-icon tp-icon-amber">
              <i class="fa-solid fa-clock"></i>
            </div>
            <h3 class="tp-feature-title">Time Logging & Worklogs</h3>
            <p class="tp-feature-desc">
              Ghi nhận số giờ làm việc thực tế trên từng task, kiểm soát ngân sách thời gian và xuất báo cáo minh bạch.
            </p>
          </div>

          <div class="tp-feature-card">
            <div class="tp-feature-icon tp-icon-sky">
              <i class="fa-solid fa-video"></i>
            </div>
            <h3 class="tp-feature-title">Cuộc họp & Thảo luận</h3>
            <p class="tp-feature-desc">
              Tích hợp tạo cuộc họp trực tuyến, ghi biên bản họp (Minutes), lưu trữ file ghi âm và biến nội dung thảo luận thành Task ngay lập tức.
            </p>
          </div>

          <div class="tp-feature-card">
            <div class="tp-feature-icon tp-icon-rose">
              <i class="fa-solid fa-user-shield"></i>
            </div>
            <h3 class="tp-feature-title">Phân quyền Vai trò (Roles & ACL)</h3>
            <p class="tp-feature-desc">
              Thiết lập vai trò linh hoạt (Project Lead, Developer, QA, Client), kiểm soát quyền xem, chỉnh sửa và quản trị chặt chẽ.
            </p>
          </div>
        </div>
      </div>
    </section>

    <!-- SOLUTIONS SECTION -->
    <section class="tp-solutions-section" id="solutions">
      <div class="tp-container">
        <div class="tp-section-header">
          <span class="tp-section-eyebrow">GIẢI PHÁP THEO ĐỘI NGŨ</span>
          <h2 class="tp-section-title">May đo hoàn hảo cho mọi quy mô & chuyên môn.</h2>
          <p class="tp-section-subtitle">
            Dù bạn là Software Team, Product Manager, Marketing Agency hay Ban điều hành — TaskPilot mang lại giải pháp tùy biến linh hoạt nhất.
          </p>
        </div>

        <div class="tp-solutions-grid">
          <div class="tp-solution-card">
            <div>
              <div class="tp-solution-badge tp-solution-badge-blue">
                <i class="fa-solid fa-code"></i> Dành cho Tech & Engineering Teams
              </div>
              <h3>Phát triển phần mềm chuẩn Agile & Scrum</h3>
              <p class="tp-solution-desc">
                Tối ưu hóa quy trình release với Sprint Backlog, quản lý Bug, Story Points và lộ trình Gantt real-time cho các Kỹ sư phần mềm.
              </p>
              <ul class="tp-solution-list">
                <li><i class="fa-solid fa-circle-check"></i> Lập kế hoạch Sprint & phân bổ Story Points</li>
                <li><i class="fa-solid fa-circle-check"></i> Theo dõi Bug & phân cấp Sub-tasks rõ ràng</li>
                <li><i class="fa-solid fa-circle-check"></i> Kéo thả mượt mà trên sơ đồ Gantt 60fps</li>
              </ul>
            </div>
          </div>

          <div class="tp-solution-card">
            <div>
              <div class="tp-solution-badge tp-solution-badge-purple">
                <i class="fa-solid fa-layer-group"></i> Dành cho Product Managers & Product Owners
              </div>
              <h3>Lộ trình tính năng & Quản lý Epic minh bạch</h3>
              <p class="tp-solution-desc">
                Theo dõi tiến độ hoàn thành các Epic lớn, làm chủ Roadmap và điều phối nguồn lực giữa các nhóm phát triển một cách dễ dàng.
              </p>
              <ul class="tp-solution-list">
                <li><i class="fa-solid fa-circle-check"></i> Tiến độ Epic tự động tính theo % công việc con</li>
                <li><i class="fa-solid fa-circle-check"></i> Chế độ xem Roadmap & Timeline linh hoạt</li>
                <li><i class="fa-solid fa-circle-check"></i> Sắp xếp ưu tiên công việc nhanh chóng</li>
              </ul>
            </div>
          </div>

          <div class="tp-solution-card">
            <div>
              <div class="tp-solution-badge tp-solution-badge-emerald">
                <i class="fa-solid fa-bullhorn"></i> Dành cho Marketing & Agencies
              </div>
              <h3>Quản lý chiến dịch & Time Tracking chi tiết</h3>
              <p class="tp-solution-desc">
                Kiểm soát thời gian thực hiện dự án khách hàng, theo dõi lịch biên tập nội dung và báo cáo giờ làm việc minh bạch.
              </p>
              <ul class="tp-solution-list">
                <li><i class="fa-solid fa-circle-check"></i> Ghi nhận Worklogs & thời gian làm việc chuẩn xác</li>
                <li><i class="fa-solid fa-circle-check"></i> Quản lý dự án theo từng Công ty / Khách hàng</li>
                <li><i class="fa-solid fa-circle-check"></i> Đỉnh cao tương tác trên Kanban & Board View</li>
              </ul>
            </div>
          </div>

          <div class="tp-solution-card">
            <div>
              <div class="tp-solution-badge tp-solution-badge-sky">
                <i class="fa-solid fa-user-gear"></i> Dành cho Executive & C-Level
              </div>
              <h3>Báo cáo tổng quan & Phân quyền bảo mật</h3>
              <p class="tp-solution-desc">
                Cung cấp bức tranh toàn cảnh về sức khỏe dự án, kiểm soát quyền truy cập chi tiết và bảo mật dữ liệu doanh nghiệp.
              </p>
              <ul class="tp-solution-list">
                <li><i class="fa-solid fa-circle-check"></i> Báo cáo tổng quan đa dự án (Multi-project Dashboard)</li>
                <li><i class="fa-solid fa-circle-check"></i> Phân quyền linh hoạt theo vai trò (Roles & ACL)</li>
                <li><i class="fa-solid fa-circle-check"></i> Tích hợp lưu trữ biên bản & âm thanh cuộc họp</li>
              </ul>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- BOTTOM CTA SECTION -->
    <section class="tp-cta-section">
      <div class="tp-container">
        <div class="tp-cta-box">
          <h2 class="tp-cta-title">Sẵn sàng nâng tầm quản lý dự án cùng TaskPilot?</h2>
          <p class="tp-cta-subtitle">
            Bắt đầu ngay hôm nay để trải nghiệm quy trình làm việc hiện đại, mượt mà và tập trung nhất cho đội ngũ của bạn.
          </p>

          <div>
            <template v-if="isAuthenticated">
              <a :href="homeUrl" class="btn-tp btn-tp-primary btn-lg" style="font-size: 16px; padding: 16px 36px;">
                <i class="fa-solid fa-compass"></i> Truy cập Workspace
              </a>
            </template>
            <template v-else>
              <a :href="registerUrl" class="btn-tp btn-tp-primary btn-lg" style="font-size: 16px; padding: 16px 36px;">
                Đăng ký miễn phí ngay <i class="fa-solid fa-arrow-right"></i>
              </a>
            </template>
          </div>
        </div>
      </div>
    </section>

    <!-- FOOTER -->
    <footer class="tp-footer">
      <div class="tp-container">
        <div class="tp-footer-inner">
          <a :href="welcomeUrl" class="tp-welcome-brand">
            <div class="tp-welcome-brand-icon">
              <i class="fa-solid fa-square-check"></i>
            </div>
            <span>TaskPilot</span>
          </a>

          <div style="font-size: 14px; color: #94a3b8;">
            Hệ thống Workspace Quản lý Dự án Hiện đại
          </div>
        </div>

        <div class="tp-footer-bottom">
          &copy; {{ currentYear }} TaskPilot. Tất cả quyền được bảo lưu. Thiết kế dành cho trải nghiệm tối ưu.
        </div>
      </div>
    </footer>
  </div>
</template>

<script setup>
import { ref } from 'vue';

const props = defineProps({
  isAuthenticated: { type: Boolean, default: false },
  userName: { type: String, default: '' },
  homeUrl: { type: String, default: '/home' },
  loginUrl: { type: String, default: '/login' },
  registerUrl: { type: String, default: '/register' },
  welcomeUrl: { type: String, default: '/' },
});

const currentYear = ref(new Date().getFullYear());

function scrollToSection(sectionId) {
  const section = document.getElementById(sectionId);

  if (!section) {
    return;
  }

  section.scrollIntoView({ behavior: 'smooth', block: 'start' });
}
</script>

<style>
.tp-solutions-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 30px;
}

@media (max-width: 768px) {
    .tp-solutions-grid {
        grid-template-columns: 1fr;
    }
}
</style>
