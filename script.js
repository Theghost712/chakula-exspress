const signupButton= document.getElementById('signupButton');
const signinButton= document.getElementById('signinButton');

const signinForm= document.getElementById('signinForm');
const signupForm= document.getElementById('signupForm');


signupButton.addEventListener('click',function()
{signupForm.style.display="none";
    signinForm.style.display="block";});
signinButton.addEventListener('click',function()
{signinForm.style.display="block";
    signupForm.style.display="none";});

    
