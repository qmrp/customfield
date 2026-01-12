<form class="custom-field-form">
    @foreach($fields as $field)
        <div class="form-group">
            <label for="field_{{ $field['key'] }}">
                {{ $field['name'] }}
            </label>
            
            @switch($field['type'])
                @case('basic')
                    <input 
                        type="text" 
                        id="field_{{ $field['key'] }}" 
                        name="{{ $field['key'] }}"
                        value="{{ $model ? ($model->{$field['config']['source_field']} ?? '') : '' }}"
                        class="form-control"
                    >
                    @break
                    
                @case('computed')
                    <div class="form-control computed-field">
                        {{ $model ? $model->getCustomField($field['key']) : '' }}
                    </div>
                    @break
                    
                @case('relation')
                    <select 
                        id="field_{{ $field['key'] }}" 
                        name="{{ $field['key'] }}"
                        class="form-control"
                    >
                        <option value="">请选择</option>
                    </select>
                    @break
                    
                @case('formatted')
                    <input 
                        type="text" 
                        id="field_{{ $field['key'] }}" 
                        name="{{ $field['key'] }}"
                        value="{{ $model ? ($model->{$field['config']['source_field']} ?? '') : '' }}"
                        class="form-control"
                    >
                    @break
                    
                @case('conditional')
                    <div class="form-control conditional-field">
                        {{ $model ? $model->getCustomField($field['key']) : '' }}
                    </div>
                    @break
                    
                @default
                    <input 
                        type="text" 
                        id="field_{{ $field['key'] }}" 
                        name="{{ $field['key'] }}"
                        class="form-control"
                    >
            @endswitch
        </div>
    @endforeach
</form>

<style>
    .custom-field-form .form-group {
        margin-bottom: 15px;
    }
    .custom-field-form label {
        display: block;
        margin-bottom: 5px;
        font-weight: 600;
    }
    .custom-field-form .form-control {
        width: 100%;
        padding: 8px 12px;
        border: 1px solid #ddd;
        border-radius: 4px;
        font-size: 14px;
    }
    .custom-field-form .computed-field,
    .custom-field-form .conditional-field {
        background-color: #f9f9f9;
        padding: 8px 12px;
    }
</style>
