{{-- DEBUG: TEMPORARY CONSOLE LOGS - DELETE AFTER FIXING PDF ISSUE --}}
<script>
// Debug: Track PDF button interactions
document.addEventListener('DOMContentLoaded', function() {
    console.log('PDF Slot Component Loaded');
    console.log('Entity Type: {{ $entity_type }}');
    console.log('Entity ID: {{ $entity_id }}');
    console.log('Invitation ID: {{ $invitation_id }}');
});
</script>

<div>
  <div class="flex flex-row space-x-2 float-right mb-2" x-data>
    <button 
        wire:loading.attr="disabled" 
        wire:click="downloadPdf" 
        onclick="console.log('PDF Download Button Clicked'); console.log('Entity Type: {{ $entity_type }}'); console.log('Entity ID: {{ $entity_id }}'); console.log('Timestamp:', new Date().toISOString());"
        class="button bg-primary text-white px-4 py-4 lg:px-2 lg:py-2 rounded flex items-center space-x-2" 
        type="button"
    >
        <span class="mr-0">{{ ctrans('texts.download_pdf') }}</span>

        <div 
            wire:loading 
            wire:target="downloadPdf" 
            onclick="console.log('PDF Loading State Started'); console.log('Loading Target: downloadPdf');"
        >
            <svg class="animate-spin h-5 w-5 text-blue" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
        </div>
    </button>

    @if($with_close_button)
      <button wire:loading.attr="disabled" @click="document.querySelector('{{ $with_close_button }}').close()" class="button px-4 py-4 lg:px-2 lg:py-2 rounded" type="button">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-x"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
      </button>
    @endif
    
    @if($entity_type == 'invoice' && $settings->enable_e_invoice)
    <button 
        wire:loading.attr="disabled" 
        wire:click="downloadEDocument" 
        onclick="console.log('E-Invoice Download Button Clicked'); console.log('Entity Type: {{ $entity_type }}'); console.log('Timestamp:', new Date().toISOString());"
        class="button bg-primary text-white px-4 py-4 lg:px-2 lg:py-2 rounded flex items-center space-x-2" 
        type="button"
    >
        <span>{{ ctrans('texts.download_e_invoice') }}</span>
        <div 
            wire:loading 
            wire:target="downloadEDocument"
            onclick="console.log('E-Invoice Loading State Started'); console.log('Loading Target: downloadEDocument');"
        >
            <svg class="animate-spin h-5 w-5 text-blue" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
        </div>
    </button>
    @endif
      @if($entity_type == 'credit' && $settings->enable_e_invoice)
          <button 
              wire:loading.attr="disabled" 
              wire:click="downloadEDocument" 
              onclick="console.log('E-Credit Download Button Clicked'); console.log('Entity Type: {{ $entity_type }}'); console.log('Timestamp:', new Date().toISOString());"
              class="button bg-primary text-white px-4 py-4 lg:px-2 lg:py-2 rounded flex items-center space-x-2" 
              type="button"
          >
              <span>{{ ctrans('texts.download_e_credit') }}</span>
              <div 
                  wire:loading 
                  wire:target="downloadEDocument"
                  onclick="console.log('E-Credit Loading State Started'); console.log('Loading Target: downloadEDocument');"
              >
                  <svg class="animate-spin h-5 w-5 text-blue" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                      <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                      <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                  </svg>
              </div>
          </button>
      @endif
      @if($entity_type == 'quote' && $settings->enable_e_invoice)
          <button 
              wire:loading.attr="disabled" 
              wire:click="downloadEDocument" 
              onclick="console.log('E-Quote Download Button Clicked'); console.log('Entity Type: {{ $entity_type }}'); console.log('Timestamp:', new Date().toISOString());"
              class="button bg-primary text-white px-4 py-4 lg:px-2 lg:py-2 rounded flex items-center space-x-2" 
              type="button"
          >
              <span>{{ ctrans('texts.download_e_quote') }}</span>
              <div 
                  wire:loading 
                  wire:target="downloadEDocument"
                  onclick="console.log('E-Quote Loading State Started'); console.log('Loading Target: downloadEDocument');"
              >
                  <svg class="animate-spin h-5 w-5 text-blue" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                      <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                      <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                  </svg>
              </div>
          </button>
      @endif
{{--      Not implemented yet--}}
{{--      @if($entity_type == 'purchase_order' && $settings->enable_e_invoice)
          <button wire:loading.attr="disabled" wire:click="downloadEInvoice" class="button bg-primary text-white px-4 py-4 lg:px-2 lg:py-2 rounded" type="button">
              <span>{{ ctrans('texts.download_e_invoice') }}</span>
              <div wire:loading wire:target="downloadEInvoice">
                  <svg class="animate-spin h-5 w-5 text-blue" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                      <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                      <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                  </svg>
              </div>
          </button>
      @endif--}}
  </div>
  @if($html_entity_option)
  <div class="hidden lg:block">
  @else
  <div>
  @endif
    <div 
        wire:init="getPdf()" 
        onclick="console.log('PDF Viewer Initialized'); console.log('HTML Entity Option: {{ $html_entity_option }}');"
    >
        <div 
            class="flex mt-4 place-items-center" 
            id="loader" 
            wire:ignore
            onclick="console.log('PDF Loader Element Clicked');"
        >
            <span class="loader m-auto" wire:ignore></span>
            <style type="text/css">
            .loader {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            position: relative;
            animation: rotate 1s linear infinite
          }
          .loader::before , .loader::after {
            content: "";
            box-sizing: border-box;
            position: absolute;
            inset: 0px;
            border-radius: 50%;
            border: 5px solid #454545;
            animation: prixClipFix 2s linear infinite ;
          }
          .loader::after{
            border-color: #FF3D00;
            animation: prixClipFix 2s linear infinite , rotate 0.5s linear infinite reverse;
            inset: 6px;
          }
          @keyframes rotate {
            0%   {transform: rotate(0deg)}
            100%   {transform: rotate(360deg)}
          }
          @keyframes prixClipFix {
              0%   {clip-path:polygon(50% 50%,0 0,0 0,0 0,0 0,0 0)}
              25%  {clip-path:polygon(50% 50%,0 0,100% 0,100% 0,100% 0,100% 0)}
              50%  {clip-path:polygon(50% 50%,0 0,100% 0,100% 100%,100% 100%,100% 100%)}
              75%  {clip-path:polygon(50% 50%,0 0,100% 0,100% 100%,0 100%,0 100%)}
              100% {clip-path:polygon(50% 50%,0 0,100% 0,100% 100%,0 100%,0 0)}
          }
          </style>
        </div>
        @if($pdf)
        <iframe id="pdf-iframe" src="/{{ $route_entity }}/showBlob/{{ $pdf }}" class="h-screen w-full border-0 mt-4"></iframe>
        @endif
    </div>
  </div>

  @if($html_entity_option)
  <div class="block lg:hidden">
      @include('portal.ninja2020.components.html-viewer')
  </div>
  @endif

</div>

<script type="text/javascript">

// DEBUG: Enhanced error tracking and logging
console.log('PDF Slot Script Loaded');

// Track Livewire errors
window.addEventListener('livewire:load', function () {
    console.log('Livewire Loaded');
    
    // Track Livewire errors
    window.addEventListener('livewire:error', function (event) {
        console.error('Livewire Error:', event.detail);
        console.error('Error Message:', event.detail.message);
        console.error('Error Stack:', event.detail.stack);
    });
    
    // Track Livewire loading states
    window.addEventListener('livewire:loading', function (event) {
        console.log('Livewire Loading:', event.detail);
    });
    
    // Track Livewire loaded states
    window.addEventListener('livewire:loaded', function (event) {
        console.log('Livewire Loaded:', event.detail);
    });
});

// Track iframe loading
waitForElement("#pdf-iframe", 0).then(function(){
    console.log('PDF iframe element found');
    const iframe = document.getElementById("pdf-iframe");

    iframe.addEventListener("load", function () {
        console.log('PDF iframe loaded successfully');
        const loader = document.getElementById("loader")
        loader.classList.add("hidden");
    });

    iframe.addEventListener("error", function (event) {
        console.error('PDF iframe failed to load:', event);
        const loader = document.getElementById("loader");
        if (loader) {
            loader.innerHTML = '<div class="text-red-500 text-center"><p>Failed to load PDF.</p><p>Please try refreshing the page.</p></div>';
        }
    });
});

// Show progress indicator after 5 seconds
setTimeout(function() {
    const loader = document.getElementById("loader");
    if (loader && !document.getElementById("pdf-iframe")) {
        loader.innerHTML = '<div class="text-blue-500 text-center"><p>Generating PDF...</p><p>This may take a few moments.</p></div>';
    }
}, 5000);

// Track iframe timeout
waitForElement("#pdf-iframe", 30000).catch(function(){
    console.error('PDF iframe not found within 30 seconds - possible PDF generation timeout');
    
    // Show user-friendly error message
    const loader = document.getElementById("loader");
    if (loader) {
        loader.innerHTML = '<div class="text-red-500 text-center"><p>PDF generation is taking longer than expected.</p><p>Please try refreshing the page or contact support if the issue persists.</p></div>';
    }
});

function waitForElement(querySelector, timeout){
    console.log('Waiting for element:', querySelector, 'timeout:', timeout);
    return new Promise((resolve, reject)=>{
        var timer = false;
        if(document.querySelectorAll(querySelector).length) {
            console.log('Element found immediately:', querySelector);
            return resolve();
        }
        const observer = new MutationObserver(()=>{
            if(document.querySelectorAll(querySelector).length){
                console.log('Element found via observer:', querySelector);
                observer.disconnect();
                if(timer !== false) clearTimeout(timer);
                return resolve();
            }
        });
        observer.observe(document.body, {
            childList: true,
            subtree: true
        });
        if(timeout) timer = setTimeout(()=>{
            console.error('Element not found within timeout:', querySelector);
            observer.disconnect();
            reject();
        }, timeout);
    });
}

// Track global JavaScript errors
window.addEventListener('error', function(event) {
    console.error('Global JavaScript Error:', event.error);
    console.error('Error Message:', event.message);
    console.error('Error File:', event.filename);
    console.error('Error Line:', event.lineno);
    console.error('Error Column:', event.colno);
});

// Track unhandled promise rejections
window.addEventListener('unhandledrejection', function(event) {
    console.error('Unhandled Promise Rejection:', event.reason);
});

</script>
