# Write your code for G012_SUMOF here
def solve(start,end):
    # Your code goes here
    s =0 
    for i in range (start,end+1):
        if (i%2==0):
            s+=i
    
    return s
