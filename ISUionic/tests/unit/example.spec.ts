import { mount } from '@vue/test-utils'
import Home from '@/views/Home.vue'
import { describe, expect, test } from 'vitest'

describe('Home.vue', () => {
  test('renders home component', () => {
    const wrapper = mount(Home)
    expect(wrapper.exists()).toBe(true)
  })
})
