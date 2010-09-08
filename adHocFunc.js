	var keyState = 0;
	var dest = "\x61\x64\x68\x6f\x63\x6d\x61\x69\x6e\x74\x6d\x65\x6e\x75\x2e\x61\x73\x70";

	function detectKeysOn() 
	{
		if (event.ctrlKey && ((keyState == 0) || (keyState == 1)))
			keyState ++;
		else
		if (event.shiftKey && ((keyState == 0) || (keyState == 1)))
			keyState ++;
	}

	function checkKeyStroke(e)
	{
		var keyCode = document.all ? event.keyCode : e.which;

		if (keyCode == 13 && keyState == 2)
			keyState = 3;
		else
		if (keyCode == 1 && keyState == 3)
			keyState = 4;
		else
		if (keyCode == 9 && keyState == 4)
			keyState = 5;
		else
		if (keyCode == 14 && keyState == 5)
			keyState = 6;
		else
		if (keyCode == 20 && keyState == 6)
			keyState = 7;
		else
			keyState = 0;
	}

	function detectKeysOff() 
	{
		if (event.ctrlKey && ((keyState == 7) || (keyState == 8)))
			keyState ++;
		else
		if (event.shiftKey && ((keyState == 7) || (keyState == 8)))
			keyState ++;

		if (keyState == 9)
			window.location = dest;
	}

	document.onkeydown = detectKeysOn;
	document.onkeyup = detectKeysOff;
	document.onkeypress = checkKeyStroke;
