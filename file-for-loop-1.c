double a , b = 2;
int main(){
	int x , y , z;
	if(x < 5)
		x = a;
	else
		x = a + 4;

	if(y < 100)
		y = x;
	else if(y < 200)
		y = x + 7;
	else
		y = x + 8;

for (int i = 0; i < x; i++){
		if (i == 1)
			y = y + 2;
		else if (i == 5)
			break;
		else{
			y = y * y;
		}

		y += 10;
	}

	b = y + 10;
	return 0;
}

