<div class="container text-center" id="add-voice-vlan-form" style="margin-top: 50px;">
    <div class="row">
        <div class="col-sm-6 col-sm-offset-3">

            <!-- Voice VLAN Configured -->
            <div class="panel panel-default">
                <div class="panel-heading"><strong>Voice VLAN Configured</strong></div>
            </div>

            <!-- Add Voice VLAN -->
            <div class="form-group">
                <label for="macAdd">MAC Address* <small>&lt;HHHH.HHHH.HHHH&gt;</small></label>
                <input type="text" class="form-control text-center" id="macAdd" name="macAdd" placeholder="Enter MAC to Add">
            </div>

            <div class="form-group">
                <label for="maskAdd">MAC Mask* <small>&lt;HHHH.HHHH.HHHH&gt;</small></label>
                <input type="text" class="form-control text-center" id="maskAdd" name="maskAdd" placeholder="Enter MAC Mask">
            </div>

            <div class="form-group text-center">
                <button class="btn btn-success" onclick="submitVoiceVlanData()">Apply</button>
                <button class="btn btn-default" type="reset">Reset</button>
                <button class="btn btn-info" onclick="BackVlanVoice()">Back to Voice VLAN Table</button>
            </div>

        </div>
    </div>
</div>

<div id="resultMsg" class="alert mt-3" style="display:none;"></div>


