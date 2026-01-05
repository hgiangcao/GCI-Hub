# Write your code for G001_FINMIN here
def solve(arr):
    # Your code goes here
    res = arr[0]
    for x in arr: 
        res = min(res, x)
        
    return res
