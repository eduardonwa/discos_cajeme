<div
  role="status"
  id="toaster"
  x-data="toasterHub(@js($toasts), @js($config))"
  data-align="{{ $alignment->value }}"
  data-pos="{{ $position->value }}"
>
  <template x-for="toast in toasts" :key="toast.id">
    <div
      x-show="toast.isVisible"
      x-init="$nextTick(() => toast.show($el))"

      x-transition:enter="t-enter"
      @if($alignment->is('bottom'))
        x-transition:enter-start="t-enter-start--bottom"
      @elseif($alignment->is('top'))
        x-transition:enter-start="t-enter-start--top"
      @else
        x-transition:enter-start="t-enter-start--center"
      @endif
      x-transition:enter-end="t-enter-end"
      x-transition:leave="t-leave"
      x-transition:leave-end="t-leave-end"

      class="toast {{ $position->is('center') ? 'toast--center' : '' }}"
      :class="toast.select({
        error:   'toast--error',
        info:    'toast--info',
        success: 'toast--success',
        warning: 'toast--warning'
      })"
    >
      <div
        class="toast__body {{ $alignment->is('bottom') ? 'toast__body--mt' : 'toast__body--mb' }}"
        :class="toast.select({
          error:   'toast__body--error',
          info:    'toast__body--info',
          success: 'toast__body--success',
          warning: 'toast__body--warning'
        })"
      >
        <!-- Mensaje principal -->
        <p x-html="toast.message"></p>

        <!-- Contenido adicional -->
        <template x-if="toast.showError">
          <div class="mt-2">
            <p x-html="toast.minimumAmount"></p>
          </div>
        </template>
      </div>

      @if($closeable)
        <button
          @click="toast.dispose()"
          aria-label="@lang('close')"
          class="toast__close"
          style="position:absolute; right:0; top:0; padding:.5rem"
        >
          <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
          </svg>
        </button>
      @endif
    </div>
  </template>
</div>
