@php
    $logoSrc = \Spinotek\CertificateGenerator\CertificateServiceProvider::logoDataUri();
@endphp
@if ($logoSrc !== '')
    <span class="sc-logo-badge" aria-hidden="true">
        <img src="{{ $logoSrc }}" alt="Spinotek" class="sc-logo-img">
    </span>
@endif
