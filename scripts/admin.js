/*
    Send a POST to the overdue-email route with the user id and overdue item
 */
function sendOverdueEmail(id, item){
    console.log(id, item);
    if (confirm("Send overdue email to user " + id + "?")){
        $.ajax({
            url:"overdue-email",    //the page containing php script
            type: "post",    //request type,
            data: {overdueItem: item, overdueId: id },
                success:function(result){
                console.log("Result: ", result);
                alert('Email has been sent!');
            },
                error: function(xhr, status, error) {
                console.error('Error:', error);
                alert('Sorry, something went wrong!');
            }
        });
    }
}
