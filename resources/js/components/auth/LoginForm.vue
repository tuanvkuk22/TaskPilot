<template>
  <div class="tp-login-container">
    <canvas class="tp-login-canvas" ref="loginCanvas"></canvas>

    <div class="tp-login-card">
      <div class="tp-login-brand">
        <div class="tp-login-logo">
          <i class="fa-solid fa-square-check"></i>
        </div>
        <span>TaskPilot</span>
      </div>

      <h1 class="tp-login-title">Chào mừng trở lại</h1>
      <p class="tp-login-subtitle">Đăng nhập để truy cập không gian làm việc của bạn</p>

      <div v-if="statusMessage && !statusDismissed" class="tp-login-alert tp-login-alert-success">
        <i class="fa-solid fa-circle-check"></i>
        <span>{{ statusMessage }}</span>
        <button type="button" class="tp-alert-close" @click="statusDismissed = true">
          <i class="fa-solid fa-xmark"></i>
        </button>
      </div>

      <div v-if="firstError && !errorDismissed" class="tp-login-alert tp-login-alert-error">
        <i class="fa-solid fa-circle-exclamation"></i>
        <span>{{ firstError }}</span>
        <button type="button" class="tp-alert-close" @click="errorDismissed = true">
          <i class="fa-solid fa-xmark"></i>
        </button>
      </div>

      <form class="tp-login-form" method="POST" :action="loginUrl || '/login'">
        <input type="hidden" name="_token" :value="csrfToken">

        <div class="tp-form-row">
          <label for="email" class="tp-form-label">Email</label>
          <input
            id="email"
            type="email"
            class="tp-form-control"
            :class="{ 'tp-form-control-error': errorMap.email }"
            name="email"
            :value="oldEmail"
            required
            autocomplete="email"
            autofocus
          >
          <span v-if="errorMap.email" class="tp-form-error">
            <i class="fa-solid fa-circle-exclamation"></i> {{ errorMap.email }}
          </span>
        </div>

        <div class="tp-form-row">
          <label for="password" class="tp-form-label">Mật khẩu</label>
          <div class="tp-password-wrapper">
            <input
              id="password"
              type="password"
              class="tp-form-control"
              :class="{ 'tp-form-control-error': errorMap.password }"
              name="password"
              required
              autocomplete="current-password"
              ref="passwordInput"
            >
            <button type="button" class="tp-password-toggle" tabindex="-1" @click="togglePassword">
              <i class="fa-solid" :class="showPassword ? 'fa-eye' : 'fa-eye-slash'"></i>
            </button>
          </div>
          <span v-if="errorMap.password" class="tp-form-error">
            <i class="fa-solid fa-circle-exclamation"></i> {{ errorMap.password }}
          </span>
        </div>

        <div class="tp-form-row tp-form-inline">
          <label class="tp-checkbox-wrapper">
            <input type="checkbox" name="remember" id="remember">
            <span class="tp-checkmark"></span>
            <span class="tp-check-label">Ghi nhớ đăng nhập</span>
          </label>

          <a v-if="passwordResetUrl" class="tp-login-link" :href="passwordResetUrl">
            Quên mật khẩu?
          </a>
        </div>

        <button type="submit" class="tp-btn tp-btn-primary tp-btn-login">
          <span>Đăng nhập</span>
        </button>
      </form>

      <div class="tp-login-divider">
        <span>Hoặc đăng nhập bằng</span>
      </div>

      <a
        v-if="googleRedirectUrl"
        :href="googleRedirectUrl"
        class="tp-btn tp-btn-google tp-btn-full"
      >
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24">
          <path fill="#EA4335" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92a5.06 5.06 0 0 1-2.2 3.32v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.1z"/>
          <path fill="#4285F4" d="M12 23c2.97 0 5.46-1 7.28-2.69l-3.57-2.77c-.99.66-2.25 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
          <path fill="#FBBC04" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
        </svg>
        <span>Tiếp tục với Google</span>
      </a>

      <div class="tp-login-footer">
        <span>Chưa có tài khoản?</span>
        <a
          v-if="registerUrl"
          :href="registerUrl"
          class="tp-login-footer-link"
        >
          Đăng ký ngay
        </a>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';

const props = defineProps({
  csrfToken: { type: String, default: '' },
  errors: { type: Array, default: () => [] },
  old: { type: Object, default: () => ({}) },
  loginUrl: { type: String, default: '' },
  googleRedirectUrl: { type: String, default: '' },
  passwordResetUrl: { type: String, default: '' },
  registerUrl: { type: String, default: '' },
  statusMessage: { type: String, default: '' },
});

const showPassword = ref(false);
const passwordInput = ref(null);
const loginCanvas = ref(null);
const canvasCtx = ref(null);
const points = ref([]);
const mouse = ref({ x: null, y: null });
const statusDismissed = ref(false);
const errorDismissed = ref(false);

const firstError = computed(() => props.errors.length > 0 ? props.errors[0] : '');

const errorMap = computed(() => {
  const map = {};
  props.errors.forEach(function (err) {
    const lower = err.toLowerCase();
    if (lower.includes('password') && !map.password) map.password = err;
    else if (lower.includes('email') && !map.email) map.email = err;
    else if (!map.general) map.general = err;
  });
  return map;
});

const oldEmail = computed(() => props.old.email || '');

function togglePassword() {
  showPassword.value = !showPassword.value;
  if (passwordInput.value) {
    passwordInput.value.type = showPassword.value ? 'text' : 'password';
  }
}

function resizeCanvas() {
  if (!loginCanvas.value) return;
  loginCanvas.value.width = window.innerWidth;
  loginCanvas.value.height = window.innerHeight;
  canvasCtx.value = loginCanvas.value.getContext('2d');
}

function initPoints() {
  if (!loginCanvas.value) return;
  points.value = [];
  for (let i = 0; i < 80; i++) {
    points.value.push({
      x: Math.random() * loginCanvas.value.width,
      y: Math.random() * loginCanvas.value.height,
      vx: (Math.random() - 0.5) * 0.35,
      vy: (Math.random() - 0.5) * 0.35,
      size: Math.random() * 1.6 + 0.4,
    });
  }
}

function animate() {
  const ctx = canvasCtx.value;
  if (!ctx || !loginCanvas.value) return;

  ctx.clearRect(0, 0, loginCanvas.value.width, loginCanvas.value.height);

  ctx.beginPath();
  for (let i = 0; i < points.value.length; i++) {
    const p = points.value[i];
    ctx.moveTo(p.x, p.y);
    p.x += p.vx;
    p.y += p.vy;
    if (p.x < 0 || p.x > loginCanvas.value.width) p.vx = -p.vx;
    if (p.y < 0 || p.y > loginCanvas.value.height) p.vy = -p.vy;
    ctx.lineTo(p.x, p.y);
  }
  ctx.strokeStyle = 'rgba(99, 102, 241, 0.10)';
  ctx.lineWidth = 0.8;
  ctx.stroke();

  for (let j = 0; j < points.value.length; j++) {
    ctx.beginPath();
    ctx.arc(points.value[j].x, points.value[j].y, points.value[j].size, 0, Math.PI * 2);
    ctx.fillStyle = 'rgba(139, 92, 246, 0.4)';
    ctx.fill();
  }

  if (mouse.value.x !== null) {
    ctx.beginPath();
    ctx.arc(mouse.value.x, mouse.value.y, 80, 0, Math.PI * 2);
    ctx.fillStyle = 'rgba(99, 102, 241, 0.04)';
    ctx.fill();

    for (let k = 0; k < points.value.length; k++) {
      const dx = points.value[k].x - mouse.value.x;
      const dy = points.value[k].y - mouse.value.y;
      const dist = Math.sqrt(dx * dx + dy * dy);
      if (dist < 80) {
        ctx.strokeStyle = 'rgba(99, 102, 241, ' + (0.15 - dist / 530) + ')';
        ctx.lineWidth = 0.7;
        ctx.beginPath();
        ctx.moveTo(mouse.value.x, mouse.value.y);
        ctx.lineTo(points.value[k].x, points.value[k].y);
        ctx.stroke();
      }
    }
  }

  requestAnimationFrame(animate);
}

onMounted(() => {
  resizeCanvas();
  initPoints();
  animate();

  window.addEventListener('resize', resizeCanvas);
  window.addEventListener('mousemove', function (e) {
    mouse.value.x = e.clientX;
    mouse.value.y = e.clientY;
  });
  window.addEventListener('mouseleave', function () {
    mouse.value.x = mouse.value.y = null;
  });
});
</script>
