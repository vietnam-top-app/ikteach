(function(){
    tinymce.PluginManager.add("ik_audio_button", function(editor, url) {
        editor.addButton("ik_audio_button", {
            title: "Insert audio",
            icon: 'icon dashicons-format-audio',
            onclick: function(){
                editor.windowManager.open({
					title: 'Insert audio',
					width: 440,
					height: 150,
					body: [{
						type: 'textbox',
						name: 'src',
						label: 'Source'
					},
					{
						type: 'listbox',
						name: 'format',
						label: 'Format',
						values : [
							{text: 'MP3', value: 'audio/mpeg'},
							{text: 'WAV', value: 'audio/wav'}
						]
					},
					{
						type: 'listbox',
						name: 'test_mode',
						label: 'Test Mode',
						values : [
							{text: 'No', value: false},
							{text: 'Yes', value: true}
						]
					}],
					onsubmit: function(e) {
						var classes = e.data.test_mode ? ' class="test-mode"' : '';
						editor.insertContent('<audio controls' + classes + '><source src="' + e.data.src + '" type="' + e.data.format + '"></audio>');
					}
				});
            }
        });
    });
})();