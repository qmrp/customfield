<table class="custom-field-table">
    <thead>
        <tr>
            @foreach($fields as $field)
                <th class="{{ $field['is_fixed'] ? 'fixed-column' : '' }}">
                    {{ $field['name'] }}
                </th>
            @endforeach
        </tr>
    </thead>
    <tbody>
        @if($data instanceof Illuminate\Database\Eloquent\Collection)
            @foreach($data as $item)
                <tr>
                    @foreach($fields as $field)
                        <td>
                            @if($item instanceof Qmrp\CustomField\Traits\HasCustomFields)
                                {{ $item->getCustomField($field['key']) ?? '-' }}
                            @else
                                {{ $item->{$field['key']} ?? '-' }}
                            @endif
                        </td>
                    @endforeach
                </tr>
            @endforeach
        @elseif(is_array($data))
            @foreach($data as $item)
                <tr>
                    @foreach($fields as $field)
                        <td>
                            @if(is_object($item) && $item instanceof Qmrp\CustomField\Traits\HasCustomFields)
                                {{ $item->getCustomField($field['key']) ?? '-' }}
                            @elseif(is_object($item))
                                {{ $item->{$field['key']} ?? '-' }}
                            @else
                                {{ $item[$field['key']] ?? '-' }}
                            @endif
                        </td>
                    @endforeach
                </tr>
            @endforeach
        @else
            <tr>
                @foreach($fields as $field)
                    <td>
                        @if($data instanceof Qmrp\CustomField\Traits\HasCustomFields)
                            {{ $data->getCustomField($field['key']) ?? '-' }}
                        @else
                            {{ $data->{$field['key']} ?? '-' }}
                        @endif
                    </td>
                @endforeach
            </tr>
        @endif
    </tbody>
</table>

<style>
    .custom-field-table {
        width: 100%;
        border-collapse: collapse;
    }
    .custom-field-table th,
    .custom-field-table td {
        padding: 8px 12px;
        border: 1px solid #ddd;
        text-align: left;
    }
    .custom-field-table th {
        background-color: #f5f5f5;
        font-weight: 600;
    }
    .custom-field-table .fixed-column {
        position: sticky;
        left: 0;
        background-color: #f5f5f5;
        z-index: 1;
    }
</style>
