<style>
    #navlink:hover {
        background-color: #fff !important;
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.2);
        border-radius: 0.5rem
    }

    #navlink:hover #icon-container {
        background-color: #ea0606 !important;
    }

    #navlink:hover #icon-container i {
        color: #fff !important;
    }
</style>

@props(['icon', 'active' => false])

@php
    $classes = $active ? 'nav-link active' : 'nav-link';
    $iconColor = $active ? 'text-white' : 'text-dark';
@endphp

<li class="nav-item">
    <a id="navlink" {{ $attributes->merge(['class' => $classes, 'aria-current' => $active ? 'page' : false]) }}>
        <div id="icon-container"
            class="icon icon-shape icon-sm shadow border-radius-md bg-white text-center me-2 d-flex align-items-center justify-content-center">
            <i class="fa-solid {{ $icon }} fa-lg {{ $iconColor }}"></i>
        </div>
        <span class="nav-link-text ms-1">{{ $slot }}</span>
    </a>
</li>
