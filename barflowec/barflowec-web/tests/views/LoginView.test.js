import { describe, it, expect, vi, beforeEach } from 'vitest'
import { render, screen, fireEvent, waitFor } from '@testing-library/vue'
import { setActivePinia, createPinia } from 'pinia'
import { createRouter, createWebHistory } from 'vue-router'
import LoginView from '@/views/LoginView.vue'
import { useAuthStore } from '@/stores/auth'

// Mocks
vi.mock('@/services/api', () => ({
  default: {
    get: vi.fn(),
    post: vi.fn(),
    defaults: { withCredentials: true },
  },
}))

const mockRouter = createRouter({
  history: createWebHistory(),
  routes: [
    { path: '/login', name: 'login', component: LoginView },
    { path: '/', name: 'dashboard', component: { template: '<div>Dashboard</div>' } },
  ],
})

function renderLogin() {
  const pinia = createPinia()
  setActivePinia(pinia)

  return render(LoginView, {
    global: {
      plugins: [mockRouter, pinia],
    },
  })
}

describe('LoginView', () => {
  beforeEach(() => {
    vi.clearAllMocks()
    mockRouter.push('/login')
  })

  it('renders login form', () => {
    renderLogin()

    expect(screen.getByText('BarFlowEC')).toBeTruthy()
    expect(screen.getByText('Iniciar sesión')).toBeTruthy()
    expect(screen.getByText('Panel administrativo comercial')).toBeTruthy()
  })

  it('has pre-filled demo credentials', () => {
    renderLogin()

    const emailInput = screen.getByDisplayValue('admin@barflowec.com')
    const passwordInput = screen.getByDisplayValue('password')

    expect(emailInput).toBeTruthy()
    expect(passwordInput).toBeTruthy()
  })

  it('calls login on form submit', async () => {
    const api = await import('@/services/api')

    api.default.get.mockResolvedValueOnce({}) // CSRF cookie
    api.default.post.mockResolvedValueOnce({
      data: { user: { id: 1, email: 'admin@barflowec.com' } },
    })

    renderLogin()

    const button = screen.getByText('Entrar')
    await fireEvent.click(button)

    await waitFor(() => {
      expect(api.default.post).toHaveBeenCalledWith('/login', {
        email: 'admin@barflowec.com',
        password: 'password',
      })
    })
  })

  it('shows error on login failure', async () => {
    const api = await import('@/services/api')

    api.default.get.mockResolvedValueOnce({}) // CSRF
    api.default.post.mockRejectedValueOnce(new Error('Invalid credentials'))

    renderLogin()

    const button = screen.getByText('Entrar')
    await fireEvent.click(button)

    await waitFor(() => {
      expect(screen.getByText('Credenciales inválidas o API no disponible.')).toBeTruthy()
    })
  })

  it('redirects to dashboard on successful login', async () => {
    const api = await import('@/services/api')

    api.default.get.mockResolvedValueOnce({}) // CSRF
    api.default.post.mockResolvedValueOnce({
      data: { user: { id: 1, email: 'admin@barflowec.com' } },
    })

    renderLogin()

    const button = screen.getByText('Entrar')
    await fireEvent.click(button)

    await waitFor(() => {
      expect(mockRouter.currentRoute.value.name).toBe('dashboard')
    })
  })
})
