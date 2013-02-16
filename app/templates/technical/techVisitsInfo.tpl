<div id='techVisitsInfo'>
	<input type='button' class='button' id='btn_techVisitsEdit' value='Editar' />
	<input type='button' class='button' id='btn_techVisitsPrint' value='Imprimir' />

	<iframe name='fra_techVisitsPDF' id='techVisitsPDF'></iframe>
	<iframe name='fra_techVisitsPrintPDF' id='techVisitsPrintPDF'></iframe>
</div>

{if $Permits->can('adminTechNotes')}
    <div id='adminTechNotes'>
        <h3>Notas de Admin</h3>
        <textarea>{$adminNote}</textarea>
        <p>
            <input type='button' id='saveAdminTechNotes' value='Guardar Nota'>
        </p>
    </div>
{/if}