import { createRouter, createWebHistory } from '@ionic/vue-router';
import { RouteRecordRaw } from 'vue-router';
import { useAuthStore } from '../stores/auth';

// Lazy load views
const Login = () => import('../views/Login.vue');
const Register = () => import('../views/Register.vue');
const ForgotPassword = () => import('../views/ForgotPassword.vue');
const Home = () => import('../views/Home.vue');
const Venues = () => import('../views/Venues.vue');
const VenueDetail = () => import('../views/VenueDetail.vue');
const Reservations = () => import('../views/Reservations.vue');
const ReservationDetail = () => import('../views/ReservationDetail.vue');
const CreateReservation = () => import('../views/CreateReservation.vue');
const Calendar = () => import('../views/Calendar.vue');
const EmergencyReports = () => import('../views/EmergencyReports.vue');
const CreateEmergency = () => import('../views/CreateEmergency.vue');
const Profile = () => import('../views/Profile.vue');

const routes: Array<RouteRecordRaw> = [
  {
    path: '/',
    redirect: '/home'
  },
  {
    path: '/login',
    name: 'Login',
    component: Login,
    meta: { requiresAuth: false }
  },
  {
    path: '/register',
    name: 'Register',
    component: Register,
    meta: { requiresAuth: false }
  },
  {
    path: '/forgot-password',
    name: 'ForgotPassword',
    component: ForgotPassword,
    meta: { requiresAuth: false }
  },
  {
    path: '/home',
    name: 'Home',
    component: Home,
    meta: { requiresAuth: false }
  },
  {
    path: '/venues',
    name: 'Venues',
    component: Venues,
    meta: { requiresAuth: false }
  },
  {
    path: '/venues/:id',
    name: 'VenueDetail',
    component: VenueDetail,
    meta: { requiresAuth: false }
  },
  {
    path: '/reservations',
    name: 'Reservations',
    component: Reservations,
    meta: { requiresAuth: false }
  },
  {
    path: '/reservations/create',
    name: 'CreateReservation',
    component: CreateReservation,
    meta: { requiresAuth: true }
  },
  {
    path: '/reservations/:id',
    name: 'ReservationDetail',
    component: ReservationDetail,
    meta: { requiresAuth: false }
  },
  {
    path: '/reservations/:id/edit',
    name: 'EditReservation',
    component: CreateReservation,
    meta: { requiresAuth: true }
  },
  {
    path: '/calendar',
    name: 'Calendar',
    component: Calendar,
    meta: { requiresAuth: false }
  },
  {
    path: '/emergency',
    name: 'EmergencyReports',
    component: EmergencyReports,
    meta: { requiresAuth: false }
  },
  {
    path: '/emergency/create',
    name: 'CreateEmergency',
    component: CreateEmergency,
    meta: { requiresAuth: true }
  },
  {
    path: '/emergency/:id',
    name: 'EmergencyDetail',
    component: EmergencyReports,
    meta: { requiresAuth: false }
  },
  {
    path: '/profile',
    name: 'Profile',
    component: Profile,
    meta: { requiresAuth: true }
  }
];

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes
});

// Navigation guard - Require authentication for protected routes
router.beforeEach(async (to, from, next) => {
  // Get auth store instance
  const authStore = useAuthStore();
  
  // Wait for auth initialization if not yet initialized
  if (!authStore.isInitialized) {
    await authStore.checkAuth();
  }
  
  // If accessing root, always redirect to home (dashboard)
  if (to.path === '/') {
    next('/home');
    return;
  }
  
  // Check if this is a page refresh (from.name is null or from.path === to.path)
  const isRefresh = !from.name || from.path === to.path;
  
  // If authenticated and it's a refresh, always redirect to dashboard
  if (isRefresh && authStore.isAuthenticated && to.path !== '/home') {
    next('/home');
    return;
  }
  
  // Check if route requires authentication
  const requiresAuth = to.matched.some(record => record.meta.requiresAuth);
  
  if (requiresAuth && !authStore.isAuthenticated) {
    // Redirect to login if not authenticated
    next({
      path: '/login',
      query: { redirect: to.fullPath }
    });
  } else if (!requiresAuth && authStore.isAuthenticated && (to.path === '/login' || to.path === '/register')) {
    // Redirect to home if already authenticated and trying to access login/register
    next('/home');
  } else {
    next();
  }
});

export default router;
