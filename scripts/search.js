/*
    Fills in the item information for the add item modal
 */
$('#addModal').on('show.bs.modal', function (event) {
    var button = $(event.relatedTarget);
    var book = button.data('book');
    //console.log(book);

    var modal = $(this);
    //modal.find('#bookData').val(JSON.stringify(book));

    // Set values of book details. These values will be submitted with POST
    modal.find('#modal-header-title').text(book.title);
    modal.find('#modal-item-title').val(book.title);
    modal.find('#modal-item-description').val(book.description);
    modal.find('#modal-item-publishedDate').val(book.publishedDate);
    modal.find('#modal-item-authors').val(book.authors);
    modal.find('#modal-item-pages').val(book.pages);
    modal.find('#modal-item-isbn').val(book.isbn);
    modal.find('#modal-item-cover').val(book.cover);
    modal.find('#modal-item-cover-preview').attr("src", book.cover);
});