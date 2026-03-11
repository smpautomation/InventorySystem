<template>
  <div class="welcome-wrapper">
    <div class="welcome-bg">
      <div class="orb orb-1"></div>
      <div class="orb orb-2"></div>
      <div class="grid-overlay"></div>
    </div>

    <div class="welcome-content">
      <div class="welcome-left">
        <div class="eyebrow">
          <!-- <span class="dot"></span> -->
          <!-- <span>System Ready</span> -->
        </div>
        <h1 class="welcome-title">
          <span class="welcome-line">Welcome To</span>
          <span class="system-line">Inventory System</span>
        </h1>
        <p class="welcome-sub">Click the navigation links to get started.</p>
      </div>

      <div class="welcome-right">
        <div class="quick-links">
          <Link href="/scan/single" class="chip">📱 Single Scan</Link>
          <Link href="/scan/multiple" class="chip">📱 Multiple Scan</Link>
          <Link href="/summary/inventory" class="chip">📊 Inventory Summary</Link>
          <Link href="/process-time" class="chip">⏱️ Process Time</Link>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { Link } from '@inertiajs/vue3'

export default {
  components: { Link },
  name: 'WelcomeHeader'
}
</script>

<style scoped>
.welcome-wrapper {
  position: relative;
  min-height: 160px;
  display: flex;
  align-items: center;
  overflow: hidden;
  background: #0d1b2a !important;
  border-bottom: 1px solid rgba(255,255,255,0.08);
}

.welcome-bg {
  position: absolute;
  inset: 0;
  z-index: 0;
}

.orb {
  position: absolute;
  border-radius: 50%;
  filter: blur(80px);
  opacity: 0.18;
  animation: drift 12s ease-in-out infinite;
}

.orb-1 {
  width: 350px;
  height: 350px;
  background: #1e5fa8;
  top: -120px;
  left: -80px;
}

.orb-2 {
  width: 300px;
  height: 300px;
  background: #0e3a6e;
  bottom: -100px;
  right: -60px;
  animation-delay: -5s;
}

@keyframes drift {
  0%, 100% { transform: translate(0, 0) scale(1); }
  50%       { transform: translate(20px, -15px) scale(1.04); }
}

.grid-overlay {
  position: absolute;
  inset: 0;
  background-image:
    linear-gradient(rgba(255,255,255,0.02) 1px, transparent 1px),
    linear-gradient(90deg, rgba(255,255,255,0.02) 1px, transparent 1px);
  background-size: 48px 48px;
}

.welcome-content {
  position: relative;
  z-index: 1;
  width: 100%;
  max-width: 1400px;
  margin: 0 auto;
  padding: 1.5rem 2rem;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 2rem;
  animation: fadeUp 0.6s ease both;
}

@keyframes fadeUp {
  from { opacity: 0; transform: translateY(16px); }
  to   { opacity: 1; transform: translateY(0); }
}

.welcome-left {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
}

.eyebrow {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-family: 'Inter', sans-serif;
  font-size: 0.7rem;
  font-weight: 600;
  letter-spacing: 0.2em;
  text-transform: uppercase;
  color: #5ba3e0;
  margin-bottom: 0.25rem;
}

.dot {
  width: 5px;
  height: 5px;
  border-radius: 50%;
  background: #5ba3e0;
  display: inline-block;
  animation: pulse 2.5s ease-in-out infinite;
}

@keyframes pulse {
  0%, 100% { opacity: 1; transform: scale(1); }
  50%       { opacity: 0.3; transform: scale(0.6); }
}

.welcome-title {
  font-family: 'Libre Baskerville', Georgia, serif;
  display: flex;
  align-items: baseline;
  gap: 0.6rem;
  line-height: 1.1;
  margin: 0;
}

.welcome-line {
  font-size: clamp(1rem, 2vw, 1.4rem);
  font-weight: 700;
  color: rgba(255, 255, 255, 0.55);
}

.system-line {
  font-size: clamp(1.5rem, 3.5vw, 2.5rem);
  font-weight: 700;
  background: linear-gradient(120deg, #5ba3e0 0%, #ffffff 50%, #2b82cb 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
  letter-spacing: -0.02em;
}

.welcome-sub {
  font-family: 'Inter', sans-serif;
  font-size: 0.825rem;
  color: rgba(255, 255, 255, 0.4);
  margin: 0;
}

.welcome-right {
  flex-shrink: 0;
}

.quick-links {
  display: flex;
  flex-wrap: wrap;
  gap: 0.5rem;
  justify-content: flex-end;
}

.chip {
  display: inline-flex;
  align-items: center;
  gap: 0.35rem;
  padding: 0.4rem 0.9rem;
  background: rgba(43, 130, 203, 0.08);
  border: 1px solid rgba(43, 130, 203, 0.25);
  border-radius: 4px;
  color: #e8f0f7;
  font-family: 'Inter', sans-serif;
  font-size: 0.8rem;
  font-weight: 500;
  text-decoration: none;
  transition: all 0.2s ease;
  white-space: nowrap;
}

.chip:hover {
  background: rgba(43, 130, 203, 0.2);
  border-color: #2b82cb;
  color: white;
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(43, 130, 203, 0.2);
}

@media (max-width: 768px) {
  .welcome-content {
    flex-direction: column;
    align-items: flex-start;
    gap: 1rem;
    padding: 1.25rem 1.5rem;
  }
  .quick-links { justify-content: flex-start; }
  .welcome-title { flex-direction: column; gap: 0; }
}
</style>
