# Write your code for G017_FINNUM here
def solve(nums,x,y):
    # Your code goes here
    for num in nums:
        if (num%x ==0 and num%y ==0):
            return num

    return -1
