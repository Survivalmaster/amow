@props(['disabled' => false])

@php($fieldName = $attributes->get('name'))
@php($disableAutofill = in_array($fieldName, ['name', 'email', 'password', 'password_confirmation', 'current_password'], true))

<input
    @disabled($disabled)
    @if ($disableAutofill && ! $attributes->has('autocomplete'))
        autocomplete="off"
        autocorrect="off"
        autocapitalize="none"
        spellcheck="false"
    @endif
    {{ $attributes->merge(['class' => 'border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm']) }}
>
