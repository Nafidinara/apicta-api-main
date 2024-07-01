<script>
import { useLayouts } from '@layouts'

export default defineComponent({
  setup(props, { slots }) {
    const { y: windowScrollY } = useWindowScroll()
    const { width: windowWidth } = useWindowSize()
    const { _layoutClasses: layoutClasses, isNavbarBlurEnabled } = useLayouts()

    const router = useRouter()
    const shallShowPageLoading = ref(false)
    
    return () => {
      // 👉 Navbar
      const navbar = h('header', { class: ['layout-navbar', { 'navbar-blur': isNavbarBlurEnabled.value }] }, [
        h('div', { class: 'navbar-content-container' }, slots.navbar?.()),
      ])

      // 👉 Content area
      let mainChildren = slots.default?.()

      // 💡 Only show loading and attach `beforeEach` & `afterEach` hooks if `content-loading` slot is used
      if (slots['content-loading']) {
        router.beforeEach(() => {
          console.info('setting to true')
          shallShowPageLoading.value = true
        })
        router.afterEach(() => {
          console.info('setting to false')
          shallShowPageLoading.value = false
        })
        mainChildren = shallShowPageLoading.value ? slots['content-loading']?.() : slots.default?.()
      }
      const main = h('main', { class: 'layout-page-content' }, h('div', { class: 'page-content-container' }, mainChildren))

      return h('div', { class: ['layout-wrapper', ...layoutClasses.value(windowWidth.value, windowScrollY.value)] }, [
        h('div', {}, [
          navbar,
          main,
        ]),
      ])
    }
  },
})
</script>

<style lang="scss">
@use "@configured-variables" as variables;
@use "@layouts/styles/placeholders";
@use "@layouts/styles/mixins";

.layout-wrapper.layout-nav-type-vertical {
  // TODO(v2): Check why we need height in vertical nav & min-height in horizontal nav
  block-size: 100%;

  .layout-content-wrapper {
    display: flex;
    flex-direction: column;
    flex-grow: 1;
    min-block-size: calc(var(--vh, 1vh) * 100);
    transition: padding-inline-start 0.2s ease-in-out;
    will-change: padding-inline-start;
  }

  .layout-navbar {
    z-index: variables.$layout-vertical-nav-layout-navbar-z-index;

    .navbar-content-container {
      block-size: variables.$layout-vertical-nav-navbar-height;
    }

    @at-root {
      .layout-wrapper.layout-nav-type-vertical {
        .layout-navbar {
          @if variables.$layout-vertical-nav-navbar-is-contained {
            @include mixins.boxed-content;
          } @else {
            .navbar-content-container {
              @include mixins.boxed-content;
            }
          }
        }
      }
    }
  }

  &.layout-navbar-sticky .layout-navbar {
    @extend %layout-navbar-sticky;
  }

  &.layout-navbar-hidden .layout-navbar {
    @extend %layout-navbar-hidden;
  }

  // 👉 Footer
  .layout-footer {
    @include mixins.boxed-content;
  }

  // 👉 Layout overlay
  .layout-overlay {
    position: fixed;
    z-index: variables.$layout-overlay-z-index;
    background-color: rgb(0 0 0 / 60%);
    cursor: pointer;
    inset: 0;
    opacity: 0;
    pointer-events: none;
    transition: opacity 0.25s ease-in-out;
    will-change: transform;

    &.visible {
      opacity: 1;
      pointer-events: auto;
    }
  }

  &:not(.layout-overlay-nav) .layout-content-wrapper {
    padding-inline-start: variables.$layout-vertical-nav-width;
  }

  // Adjust right column pl when vertical nav is collapsed
  &.layout-vertical-nav-collapsed .layout-content-wrapper {
    padding-inline-start: variables.$layout-vertical-nav-collapsed-width;
  }

  // 👉 Content height fixed
  &.layout-content-height-fixed {
    .layout-content-wrapper {
      max-block-size: calc(var(--vh) * 100);
    }

    .layout-page-content {
      display: flex;
      overflow: hidden;

      .page-content-container {
        inline-size: 100%;

        > :first-child {
          max-block-size: 100%;
          overflow-y: auto;
        }
      }
    }
  }
}
</style>
