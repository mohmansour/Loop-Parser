double a , b = 2;
int main()
{
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
int i = 0; 
 switch(1) 
 { 
 case 1:
//Loop-0- Starts
{ 

if (i == 1)
y = y + 2;
else if (i == 5)
break;
else
{
y = y * y;

}
y += 10;

 
i++;
}
{ 

if (i == 1)
y = y + 2;
else if (i == 5)
break;
else
{
y = y * y;

}
y += 10;

 
i++;
}
{ 

if (i == 1)
y = y + 2;
else if (i == 5)
break;
else
{
y = y * y;

}
y += 10;

 
i++;
}

//Loop-0- Ends
break; 
 } 
int j = 2; 
 switch(1) 
 { 
 case 1:
//Loop-1- Starts
{ 
y=j++;
 
j+=2;
}
{ 
y=j++;
 
j+=2;
}

//Loop-1- Ends
break; 
 } 
b = y + 10;
return 0;
}
