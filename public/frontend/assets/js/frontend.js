function toggleReplyForm(commentId){
    let form = document.getElementById('reply-form-' + commentId);
    if(form.style.display === 'block'){
        form.style.display = 'none';
    }else{
        document.querySelectorAll('.reply-form-box')
            .forEach(function(item){
                item.style.display = 'none';
            });

        form.style.display = 'block';
    }
}