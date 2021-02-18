/*
 * Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.twig).
 */

// any CSS you import will output into a single css file (app.css in this case)
import './styles/app.css';
import 'bootstrap/dist/css/bootstrap.css';
import 'datatables.net-bs4/css/dataTables.bootstrap4.css';
import './bootstrap';

// start the Stimulus application
import 'bootstrap/dist/js/bootstrap';
import 'datatables.net-bs4/js/dataTables.bootstrap4';

const $ = require('jquery');

$(function () {
    $('table.datatable').dataTable();
});