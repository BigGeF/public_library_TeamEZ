/*
    FIlls in the items information for the return item modal
 */
$('#returnModal').on('show.bs.modal', function (event) {
    let button = $(event.relatedTarget);
    //console.log(button);
    let book = button.data('book');
    let id = button.data('user');
    let title = button.data('title');
    let cover = button.data('cover');
    console.log(cover);

    let modal = $(this);

    // Set values of book details. These values will be submitted with POST
    modal.find('#modal-header-title').text(title);
    modal.find('#modal-item-id').val(book);
    modal.find('#modal-item-user').val(id);
    modal.find('#modal-item-cover-preview').attr("src", cover);
});