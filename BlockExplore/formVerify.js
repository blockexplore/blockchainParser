
// The event handler function to compute the total cost
//function to determine if a field is blank
var pswd;

function checkPswds(){
    
	pswd1 = document.getElementById('password1');
	pswd2 = document.getElementById('password2');
	var  re = /[0-9]/;
    if( ! re.test(pswd1.value)) {
		alert("Password must contain at least one digit");
		return false;
    }
	re = /[A-Z]/;
    if( ! re.test(pswd1.value)) {
		alert("Password must contain at least one uppercase letter");
		return false;
    }	
	re = /[a-z]/;
    if( ! re.test(pswd1.value)) {
		alert("Password must contain at least one lowercase letter");
		return false;
    }
	if( pswd1.value.length < 6) {
		alert("Password must have at least 6 characters");
		return false;
    }	
	if (pswd1.value == pswd2.value) {
		alert("Good Passwords");
		return true;
	} else {
		alert("Passwords don't match");
		return false;
	}
	
}

function isBlank(inputField){
    if(inputField.type=="checkbox"){
		if(inputField.checked)
			return false;
		return true;
    }
    if (inputField.value==""){
		return true;
    }
    return false;
}

//function to highlight an error through colour by adding css attributes tot he div passed in
function makeRed(inputDiv){
   	inputDiv.style.backgroundColor="#AA0000";
	//inputDiv.parentNode.style.backgroundColor="#AA0000";
	inputDiv.parentNode.style.color="#FFFFFF";		
}

//remove all error styles from the div passed in
function makeClean(inputDiv){
	inputDiv.parentNode.style.backgroundColor="#FFFFFF";
	inputDiv.parentNode.style.color="#000000";		
}

//the main function must occur after the page is loaded, hence being inside the wondow.onload event handler.
window.onload = function(){
    var myForm = document.getElementById("signUpForm");

    //all inputs with the class required are looped through 
    var requiredInputs = document.querySelectorAll(".required");
    for (var i=0; i < requiredInputs.length; i++){
		requiredInputs[i].onfocus = function(){
			this.style.backgroundColor = "#EEEE00";
		}
    }

    //on submitting the form, "empty" checks are performed on required inputs.
    myForm.onsubmit = function(e){
		var requiredInputs = document.querySelectorAll(".required");
		for (var i=0; i < requiredInputs.length; i++){
			if( isBlank(requiredInputs[i]) ){
				e.preventDefault();
				makeRed(requiredInputs[i]);
			}
			else{
				makeClean(requiredInputs[i]);
			} 
		}
		if ( !checkPswds() ) {
			e.preventDefault();
		} 
	}   
}