<div style="margin: 15px;">
<h4>{{ $plugin_name }} Settings:</h4>

<!-- Example of free-form settings, real plugins should use specific fields -->
<!-- All input fields should be in the settings array (settings[]) -->

<form method="post" style="margin: 15px">
    @csrf
    <table id="settings-table">
        <tr>
        <th>Name</th>
        <th>Value</th>
        </tr>
    @forelse($settings as $name => $value)
        <tr id="settings-row-{{ $name }}">
            <td>
                {{ $name }}
            </td>
            <td>
                <input id="value-{{ $value }}" type="text" name="settings[{{ $name }}]" value="{{ $value }}">
                <button id="delete-{{ $name }}" type="button" onclick="deleteSetting(this.id)" class="delete-button"><i class="fa fa-trash"></i></button>
            </td>
        </tr>
    @empty
        <tr>
            <td>No settings yet</td>
        </tr>
    @endforelse
    </table>
    <div style="margin: 15px 0;">
        <input id="new-setting-name" style="display: inline-block;" type="text" placeholder="Name">
        <input id="new-setting-value" style="display: inline-block;" type="text" placeholder="Value">
        <button type="button" onclick="newSetting()">Add Setting</button>
    </div>
    <div>
        <button type="submit">Save</button>
    </div>
</form>
</div>

<script>
    function newSetting() {
        var name = document.getElementById('new-setting-name').value;
        var value = document.getElementById('new-setting-value').value;
        var existing = document.getElementById('value-' + name);

        if (existing) {
            existing.value = value;
        } else {
            // insert setting
            var newValue = document.createElement('input');
            newValue.id = 'value-' + name;
            newValue.type = 'text';
            newValue.name = 'settings[' + name + ']';
            newValue.value = value;

            var deleteButton = document.createElement('button');
            deleteButton.type = 'button';
            deleteButton.className = 'delete-button';
            deleteButton.onclick = () => deleteSetting(name);
            var deleteIcon = document.createElement('i');
            deleteIcon.className = 'fa fa-trash';
            deleteButton.appendChild(deleteIcon);

            var row = document.createElement('tr');
            row.id = 'settings-row-' + name;
            var col1 = document.createElement('td');
            var col2 = document.createElement('td');
            col1.innerText = name;
            col2.appendChild(newValue);
            col2.appendChild(document.createTextNode(' '));
            col2.appendChild(deleteButton);
            row.appendChild(col1);
            row.appendChild(col2);
            document.getElementById('settings-table').appendChild(row);
        }

        document.getElementById('new-setting-name').value = '';
        document.getElementById('new-setting-value').value = '';
    }

    function deleteSetting(nameId) {
        document.getElementById('settings-row-' + nameId.substring(7)).remove();
    }
</script>

<style>
    #settings-table td, #settings-table th {
        padding: .2em;
    }
    .delete-button {
        padding: 3px 5px;
    }
</style>
