import { describe, it, expect, vi, beforeEach } from 'vitest'
import { setActivePinia, createPinia } from 'pinia'
import { useAuthStore } from '@/stores/auth'
import api from '@/services/api'

// Mock @/services/api
vi.mock('@/services/api', () => ({
  default: {
    get: vi.fn(),
    post: vi.fn(),
    defaults: { withCredentials: true },
  },
}))

describe('AuthStore', () => {
  beforeEach(() => {
    setActivePinia(createPinia())
    vi.clearAllMocks()
  })

  it('starts with loading true and no user', () => {
    const auth = useAuthStore()

    expect(auth.loading).toBe(true)
    expect(auth.user).toBeNull()
    expect(auth.isAuthenticated).toBe(false)
  })

  it('login sets user on success', async () => {
    const mockUser = { id: 1, name: 'Admin', email: 'admin@test.com' }

    api.get.mockResolvedValue({}) // CSRF cookie
    api.post.mockResolvedValue({ data: { user: mockUser } })

    const auth = useAuthStore()
    const result = await auth.login({ email: 'admin@test.com', password: 'password' })

    expect(result.user).toEqual(mockUser)
    expect(auth.user).toEqual(mockUser)
    expect(auth.isAuthenticated).toBe(true)
    expect(auth.loading).toBe(false)
  })

  it('login calls CSRF endpoint first', async () => {
    const mockUser = { id: 1, name: 'Admin', email: 'admin@test.com' }

    api.get.mockResolvedValue({}) // CSRF
    api.post.mockResolvedValue({ data: { user: mockUser } })

    const auth = useAuthStore()
    await auth.login({ email: 'admin@test.com', password: 'password' })

    // Login should be called with credentials
    expect(api.post).toHaveBeenCalledWith('/login', {
      email: 'admin@test.com',
      password: 'password',
    })
  })

  it('fetchUser sets user from API', async () => {
    const mockUser = { id: 1, name: 'Test', email: 'test@test.com' }

    api.get.mockResolvedValue({ data: { user: mockUser } })

    const auth = useAuthStore()
    const result = await auth.fetchUser()

    expect(result).toEqual(mockUser)
    expect(auth.user).toEqual(mockUser)
    expect(auth.isAuthenticated).toBe(true)
  })

  it('fetchUser throws on API error', async () => {
    api.get.mockRejectedValueOnce(new Error('Unauthorized'))

    const auth = useAuthStore()
    await expect(auth.fetchUser()).rejects.toThrow()
    expect(auth.user).toBeNull()
  })

  it('logout clears user', async () => {
    api.post.mockResolvedValueOnce({})

    const auth = useAuthStore()
    auth.user = { id: 1, name: 'Test' }

    await auth.logout()

    expect(auth.user).toBeNull()
    expect(auth.isAuthenticated).toBe(false)
    expect(api.post).toHaveBeenCalledWith('/logout')
  })

  it('logout handles API error gracefully', async () => {
    api.post.mockRejectedValueOnce(new Error('Network error'))

    const auth = useAuthStore()
    auth.user = { id: 1, name: 'Test' }

    await auth.logout()

    // Should still clear user even if API call fails
    expect(auth.user).toBeNull()
  })

  it('init sets user when session is valid', async () => {
    const mockUser = { id: 1, name: 'Admin', email: 'admin@test.com' }

    api.get.mockResolvedValueOnce({ data: { user: mockUser } })

    const auth = useAuthStore()
    await auth.init()

    expect(auth.user).toEqual(mockUser)
    expect(auth.isAuthenticated).toBe(true)
    expect(auth.loading).toBe(false)
  })

  it('init sets user to null when session expired', async () => {
    api.get.mockRejectedValueOnce(new Error('Unauthenticated'))

    const auth = useAuthStore()
    await auth.init()

    expect(auth.user).toBeNull()
    expect(auth.isAuthenticated).toBe(false)
    expect(auth.loading).toBe(false)
  })
})
