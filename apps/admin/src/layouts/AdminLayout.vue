<template>
  <q-layout view="lHh Lpr lFf" class="admin-layout">
    <q-drawer
      v-model="leftDrawerOpen"
      show-if-above
      :width="260"
      :breakpoint="1023"
      dark
      bordered
      class="admin-drawer"
      content-class="admin-drawer-content"
    >
      <div class="drawer-inner column full-height">
        <div class="drawer-brand">
          <img
            src="/brand/logo_horizontal.svg?v=2"
            alt="Modelarc"
            class="drawer-logo-img"
          />
        </div>

        <q-scroll-area class="col drawer-scroll">
          <div
            v-for="section in menuSections"
            :key="section.label"
            class="menu-section"
          >
            <div class="menu-section-label">{{ section.label }}</div>
            <q-list dense dark class="menu-list">
              <q-item
                v-for="item in section.items"
                :key="item.to"
                clickable
                v-ripple
                :to="item.to"
                exact
                active-class="menu-active"
                class="menu-item"
              >
                <q-item-section avatar>
                  <q-icon :name="item.icon" size="20px" />
                </q-item-section>
                <q-item-section>
                  <q-item-label>{{ item.label }}</q-item-label>
                </q-item-section>
              </q-item>
            </q-list>
          </div>
        </q-scroll-area>

        <div class="drawer-footer">
          <q-btn
            flat
            no-caps
            dark
            class="logout-btn full-width"
            icon="logout"
            label="Cerrar sesión"
            align="left"
            @click="onLogout"
          />
        </div>
      </div>
    </q-drawer>

    <q-header class="admin-header" elevated>
      <q-toolbar>
        <q-btn
          flat
          dense
          round
          icon="menu"
          aria-label="Menu"
          class="q-mr-sm text-grey-8"
          @click="toggleLeftDrawer"
        />
        <q-toolbar-title class="header-title text-weight-medium">
          {{ pageTitle }}
        </q-toolbar-title>

        <div class="header-actions row items-center no-wrap">
          <button type="button" class="header-notify" aria-label="Notificaciones">
            <q-icon name="notifications_none" size="44px" />
            <span class="header-notify__badge">3</span>
          </button>

          <q-avatar size="40px" color="primary" text-color="white" class="header-avatar">
            {{ avatarLetter }}
          </q-avatar>

          <div class="header-user gt-xs">
            <div class="text-weight-medium">{{ auth.displayName }}</div>
            <div class="text-caption text-grey-6">Administrador</div>
          </div>
        </div>
      </q-toolbar>
    </q-header>

    <q-page-container class="admin-page-container">
      <router-view />
    </q-page-container>
  </q-layout>
</template>

<script setup lang="ts">
import { computed, onMounted, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/authStore'

const leftDrawerOpen = ref(false)
const route = useRoute()
const router = useRouter()
const auth = useAuthStore()

const menuSections = [
  {
    label: 'Principal',
    items: [{ label: 'Dashboard', icon: 'dashboard', to: '/' }],
  },
  {
    label: 'Gestión',
    items: [
      { label: 'Proyectos', icon: 'apartment', to: '/proyectos' },
      { label: 'Recorridos 360°', icon: 'panorama_photosphere', to: '/recorridos' },
      { label: 'Galería de medios', icon: 'photo_library', to: '/medios' },
      { label: 'Servicios', icon: 'handyman', to: '/servicios' },
      { label: 'Testimonios', icon: 'format_quote', to: '/testimonios' },
      { label: 'Solicitudes', icon: 'mail_outline', to: '/solicitudes' },
    ],
  },
  {
    label: 'Administración',
    items: [
      { label: 'Usuarios', icon: 'group', to: '/usuarios' },
      { label: 'Manuales', icon: 'menu_book', to: '/manuales' },
      { label: 'Configuración', icon: 'settings', to: '/configuracion' },
    ],
  },
]

const pageTitle = computed(() => (route.meta.title as string) || 'Dashboard')
const avatarLetter = computed(() => (auth.displayName || 'A').charAt(0).toUpperCase())

function toggleLeftDrawer() {
  leftDrawerOpen.value = !leftDrawerOpen.value
}

async function onLogout() {
  await auth.logout()
  await router.push('/login')
}

onMounted(() => {
  if (!auth.user) {
    void auth.fetchMe()
  }
})
</script>

<style lang="scss">
/* Global (not scoped) so Quasar drawer internals inherit dark theme */
.admin-drawer.q-drawer,
.admin-drawer .q-drawer__content,
.admin-drawer-content {
  background: #111111 !important;
  color: #e8e8e8 !important;
  border-right: 1px solid rgba(255, 255, 255, 0.06) !important;
}

.admin-drawer .q-scrollarea,
.admin-drawer .q-scrollarea__container,
.admin-drawer .q-scrollarea__content {
  background: transparent !important;
  color: inherit;
}

.admin-drawer .drawer-inner {
  min-height: 100%;
  background: #111111;
}

.admin-drawer .drawer-brand {
  flex-shrink: 0;
  box-sizing: border-box;
  height: var(--ma-topbar-height, 88px);
  padding: 12px 20px;
  border-bottom: 1px solid rgba(255, 255, 255, 0.06);
  background: #1a1a1a;
  display: flex;
  align-items: center;
  justify-content: flex-start;
}

.admin-drawer .drawer-logo-img {
  display: block;
  height: 40px;
  width: auto;
  max-width: 100%;
  object-fit: contain;
  object-position: left center;
  flex-shrink: 1;
}

@media (max-width: 1023px) {
  .admin-drawer .drawer-brand {
    height: auto;
    min-height: 64px;
    padding: 10px 16px;
  }

  .admin-drawer .drawer-logo-img {
    height: 34px;
  }
}

.admin-drawer .drawer-scroll {
  min-height: 0;
}

.admin-drawer .menu-section {
  padding-top: 12px;
  padding-bottom: 4px;
}

.admin-drawer .menu-section-label {
  padding: 8px 24px 6px;
  font-size: 0.65rem;
  letter-spacing: 0.12em;
  text-transform: uppercase;
  color: rgba(255, 255, 255, 0.4);
}

.admin-drawer .menu-list {
  background: transparent !important;
  padding: 0 8px 8px;
}

.admin-drawer .menu-item {
  margin: 2px 4px;
  border-radius: 8px;
  color: rgba(255, 255, 255, 0.78) !important;
  min-height: 42px;
}

.admin-drawer .menu-item .q-icon,
.admin-drawer .menu-item .q-item__label {
  color: inherit !important;
}

.admin-drawer .menu-item:hover {
  background: rgba(255, 255, 255, 0.06) !important;
  color: #ffffff !important;
}

.admin-drawer .menu-active {
  background: rgba(196, 164, 124, 0.18) !important;
  color: #c4a47c !important;
  border-left: 3px solid #c4a47c;
}

.admin-drawer .menu-active .q-icon,
.admin-drawer .menu-active .q-item__label {
  color: #c4a47c !important;
}

.admin-drawer .drawer-footer {
  flex-shrink: 0;
  padding: 12px 16px 20px;
  border-top: 1px solid rgba(255, 255, 255, 0.06);
  background: #111111;
}

.admin-drawer .logout-btn {
  color: rgba(255, 255, 255, 0.6) !important;
}

.admin-drawer .logout-btn:hover {
  color: #ffffff !important;
  background: rgba(255, 255, 255, 0.06);
}

.admin-header {
  background: #fff !important;
  color: #2c2c2c;
  box-shadow: 0 1px 0 #e8e8e8;
  height: var(--ma-topbar-height, 88px);
}

.admin-header .q-toolbar {
  min-height: var(--ma-topbar-height, 88px) !important;
  height: var(--ma-topbar-height, 88px);
  padding-left: 16px;
  padding-right: 20px;
}

.admin-header .header-title {
  font-size: 1.05rem;
}

.admin-header .header-actions {
  gap: 12px;
}

.admin-header .header-notify {
  position: relative;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 40px;
  height: 40px;
  margin: 0;
  padding: 0;
  border: 0;
  border-radius: 50%;
  background: transparent;
  color: #4a4a4a;
  cursor: pointer;
  line-height: 0;
  overflow: visible;
}

.admin-header .header-notify:hover {
  background: rgba(0, 0, 0, 0.05);
  color: #2c2c2c;
}

/* Material Icons tienen padding interno: 44px visual ≈ avatar 40px */
.admin-header .header-notify .q-icon {
  font-size: 44px !important;
  width: 44px !important;
  height: 44px !important;
  line-height: 44px !important;
}

.admin-header .header-notify__badge {
  position: absolute;
  top: -2px;
  right: -2px;
  min-width: 18px;
  height: 18px;
  padding: 0 5px;
  border-radius: 999px;
  background: #c4a47c;
  color: #fff;
  font-size: 11px;
  font-weight: 600;
  line-height: 18px;
  text-align: center;
}

.admin-header .header-avatar {
  font-size: 1rem;
}

.admin-header .header-user {
  line-height: 1.2;
}

.admin-page-container {
  background: #f5f5f5;
  width: 100%;
}

.admin-page-container > .q-page {
  width: 100%;
  max-width: none;
}
</style>
