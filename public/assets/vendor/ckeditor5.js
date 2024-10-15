import {
    ClassicEditor,
    AccessibilityHelp,
    Autosave,
    Bold,
    Essentials,
    Italic,
    Mention,
    Paragraph,
    SelectAll,
    Undo
} from 'ckeditor5';
import { SlashCommand } from 'ckeditor5-premium-features';

const editorConfig = {
    toolbar: {
        items: ['undo', 'redo', '|', 'selectAll', '|', 'bold', 'italic', '|', 'accessibilityHelp'],
        shouldNotGroupWhenFull: false
    },
    placeholder: 'Type or paste your content here!',
    plugins: [AccessibilityHelp, Autosave, Bold, Essentials, Italic, Mention, Paragraph, SelectAll, SlashCommand, Undo],
    licenseKey: '<YOUR_LICENSE_KEY>',
    mention: {
        feeds: [
            {
                marker: '@',
                feed: [
                    /* See: https://ckeditor.com/docs/ckeditor5/latest/features/mentions.html */
                ]
            }
        ]
    },
    initialData: ""
};

ClassicEditor
    .create( document.querySelector( '#editor' ), editorConfig )
    .then( editor => {
        console.log( editor );
    } )
    .catch( error => {
        console.error( error );
    } );
